<?php

declare(strict_types=1);

namespace Zoosper\Media\Lifecycle;

use PDO;
use RuntimeException;
use Throwable;
use Zoosper\Core\Audit\AuditLoggerInterface;
use Zoosper\Media\Model\MediaAsset;
use Zoosper\Media\Repository\MediaAssetRepository;
use Zoosper\Media\Repository\MediaDerivativeRepository;
use Zoosper\Media\Service\MediaStoredFileCleanupResult;
use Zoosper\Media\Service\MediaStoredFileCleanupService;

/** Media-owned archive, restore and reference-safe permanent-delete boundary. */
final readonly class MediaLifecycleCoordinator
{
    public function __construct(
        private PDO $pdo,
        private MediaAssetRepository $assets,
        private MediaStoredFileCleanupService $cleanup,
        private MediaDerivativeRepository $derivatives,
        private MediaReferenceInspector $references,
        private ?AuditLoggerInterface $audit = null,
    ) {
    }

    public function archive(MediaAsset $asset, int $actorId, string $actorEmail): bool
    {
        if ($asset->status === 'archived') {
            return true;
        }
        $this->assets->changeStatus($asset->id, 'archived');
        $this->log($actorId, $actorEmail, 'media.archived', $asset, 'archived');
        return true;
    }

    public function restore(MediaAsset $asset, int $actorId, string $actorEmail): bool
    {
        if ($asset->status !== 'archived') {
            return false;
        }
        $this->assets->changeStatus($asset->id, 'active');
        $this->log($actorId, $actorEmail, 'media.restored', $asset, 'active');
        return true;
    }

    /** Backwards-compatible Admin boundary; guarded policy remains shared. */
    public function deletePermanently(MediaAsset $asset, int $actorId, string $actorEmail): bool
    {
        return $this->deletePermanentlyGuarded($asset, $actorId, $actorEmail)->successful;
    }

    public function deletePermanentlyGuarded(MediaAsset $asset, int $actorId, string $actorEmail): MediaLifecycleResult
    {
        if ($asset->status !== 'archived') {
            return new MediaLifecycleResult(false, 'delete', $asset->id, $asset->status, blockers: ['status' => 1], message: 'Archive Media before permanent deletion.');
        }
        $counts = $this->references->counts($asset);
        $blockers = array_filter($counts, static fn (int $count): bool => $count > 0);
        if ($blockers !== []) {
            $this->log($actorId, $actorEmail, 'media.delete_blocked', $asset, $asset->status, ['blockers' => $blockers]);
            return new MediaLifecycleResult(false, 'delete', $asset->id, $asset->status, blockers: $blockers, message: 'Remove Page references before permanent Media deletion.');
        }

        $derivatives = $this->derivatives->forAsset($asset->id);
        $ownTransaction = !$this->pdo->inTransaction();
        if ($ownTransaction) {
            $this->pdo->beginTransaction();
        }
        try {
            $this->assets->deletePermanently($asset->id);
            if ($ownTransaction) {
                $this->pdo->commit();
            }
        } catch (Throwable $exception) {
            if ($ownTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new RuntimeException('Permanent Media deletion failed and was rolled back.', previous: $exception);
        }

        $cleanup = $this->cleanup->cleanup($asset);
        foreach ($derivatives as $derivative) {
            $result = $this->cleanup->cleanup((object) ['storagePath' => $derivative->storagePath, 'publicPath' => $derivative->publicPath]);
            $cleanup = new MediaStoredFileCleanupResult(
                array_merge($cleanup->deleted, $result->deleted),
                array_merge($cleanup->skipped, $result->skipped)
            );
        }
        $this->log($actorId, $actorEmail, 'media.deleted_permanently', $asset, null, [
            'cleanup_deleted' => count($cleanup->deleted),
            'cleanup_skipped' => count($cleanup->skipped),
        ]);

        return new MediaLifecycleResult(true, 'delete', $asset->id, $asset->status);
    }

    /** @param array<string, mixed> $extra */
    private function log(int $actorId, string $actorEmail, string $action, MediaAsset $asset, ?string $status, array $extra = []): void
    {
        $this->audit?->logAction($actorId, $actorEmail, $action, 'media_asset', (string) $asset->id, $action, $extra + [
            'asset_id' => $asset->id,
            'previous_status' => $asset->status,
            'new_status' => $status,
        ]);
    }
}
