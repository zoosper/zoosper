<?php

declare(strict_types=1);

namespace Zoosper\Site\Lifecycle;

use PDO;
use RuntimeException;
use Throwable;
use Zoosper\Core\Audit\AuditLoggerInterface;
use Zoosper\Site\Model\Site;
use Zoosper\Site\Repository\SiteRepository;

/** Site-owned inactive, restore, and guarded permanent-delete boundary. */
final readonly class SiteLifecycleCoordinator
{
    public function __construct(
        private PDO $pdo,
        private SiteRepository $sites,
        private SiteReferenceInspector $references,
        private ?AuditLoggerInterface $audit = null,
    ) {}

    public function disable(Site $site, int $actorId, string $actorEmail): SiteLifecycleResult
    {
        if ($site->status === 'inactive') { return new SiteLifecycleResult(true, 'disable', $site->id, 'inactive', 'inactive', message: 'Site is already inactive.'); }
        $this->sites->updateStatus($site->id, 'inactive');
        $this->log($actorId, $actorEmail, 'site.disabled', $site, 'inactive');
        return new SiteLifecycleResult(true, 'disable', $site->id, $site->status, 'inactive');
    }

    public function restore(Site $site, int $actorId, string $actorEmail): SiteLifecycleResult
    {
        if ($site->status !== 'inactive') { return new SiteLifecycleResult(false, 'restore', $site->id, $site->status, blockers: ['status' => 1], message: 'Only inactive Sites can be restored.'); }
        $this->sites->updateStatus($site->id, 'active');
        $this->log($actorId, $actorEmail, 'site.restored', $site, 'active');
        return new SiteLifecycleResult(true, 'restore', $site->id, 'inactive', 'active');
    }

    public function deletePermanently(Site $site, int $actorId, string $actorEmail): SiteLifecycleResult
    {
        if ($site->status !== 'inactive') { return new SiteLifecycleResult(false, 'delete', $site->id, $site->status, blockers: ['status' => 1], message: 'Make the Site inactive before permanent deletion.'); }
        $blockers = array_filter($this->references->counts($site->id), static fn (int $count): bool => $count > 0);
        if ($blockers !== []) { return new SiteLifecycleResult(false, 'delete', $site->id, $site->status, blockers: $blockers, message: 'Remove or move Site references before permanent deletion.'); }
        $started = !$this->pdo->inTransaction();
        if ($started) { $this->pdo->beginTransaction(); }
        try {
            $this->sites->deletePermanently($site->id);
            if ($started) { $this->pdo->commit(); }
        } catch (Throwable $exception) {
            if ($started && $this->pdo->inTransaction()) { $this->pdo->rollBack(); }
            throw new RuntimeException('Permanent Site deletion failed and was rolled back.', previous: $exception);
        }
        $this->log($actorId, $actorEmail, 'site.deleted_permanently', $site, null);
        return new SiteLifecycleResult(true, 'delete', $site->id, 'inactive', null);
    }

    private function log(int $actorId, string $email, string $action, Site $site, ?string $newStatus): void
    {
        $this->audit?->logAction($actorId, $email, $action, 'site', (string) $site->id, $action, ['site_id' => $site->id, 'previous_status' => $site->status, 'new_status' => $newStatus]);
    }
}
