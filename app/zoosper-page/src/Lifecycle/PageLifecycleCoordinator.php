<?php

declare(strict_types=1);

namespace Zoosper\Page\Lifecycle;

use PDO;
use RuntimeException;
use Throwable;
use Zoosper\Core\Audit\AuditLoggerInterface;
use Zoosper\Page\Model\Page;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Repository\PageRevisionRepository;
use Zoosper\Page\Service\PageRevisionService;

/** Page-owned archive, restore and guarded permanent-delete transaction boundary. */
final readonly class PageLifecycleCoordinator
{
    public function __construct(
        private PDO $pdo,
        private PageRepository $pages,
        private PageRevisionService $revisions,
        private PageRevisionRepository $revisionRows,
        private PageReferenceInspector $references,
        private ?AuditLoggerInterface $audit = null,
    ) {}

    public function archive(Page $page, int $actorId, string $actorEmail): PageLifecycleResult
    {
        if ($page->status === 'archived') {
            return new PageLifecycleResult(true, 'archive', $page->id, 'archived', 'archived', message: 'Page is already archived.');
        }
        $this->revisions->capturePage($page, $actorId);
        $this->pages->archive($page->id, $actorId);
        $this->log($actorId, $actorEmail, 'page.archived', $page, 'archived');
        return new PageLifecycleResult(true, 'archive', $page->id, $page->status, 'archived');
    }

    public function restore(Page $page, int $actorId, string $actorEmail): PageLifecycleResult
    {
        if ($page->status !== 'archived') {
            return new PageLifecycleResult(false, 'restore', $page->id, $page->status, blockers: ['status' => 1], message: 'Only archived Pages can be restored.');
        }
        $this->revisions->capturePage($page, $actorId);
        $this->pages->restoreArchived($page->id, $actorId);
        $this->log($actorId, $actorEmail, 'page.restored', $page, 'draft');
        return new PageLifecycleResult(true, 'restore', $page->id, 'archived', 'draft');
    }

    public function deletePermanently(Page $page, int $actorId, string $actorEmail): PageLifecycleResult
    {
        if ($page->status !== 'archived') {
            return new PageLifecycleResult(false, 'delete', $page->id, $page->status, blockers: ['status' => 1], message: 'Archive the Page before permanent deletion.');
        }
        $counts = $this->references->counts($page->id);
        $blockers = array_filter($counts, static fn (int $count): bool => $count > 0);
        if ($blockers !== []) {
            return new PageLifecycleResult(false, 'delete', $page->id, $page->status, blockers: $blockers, message: 'Remove Page references before permanent deletion.');
        }
        $started = !$this->pdo->inTransaction();
        if ($started) { $this->pdo->beginTransaction(); }
        try {
            $this->revisionRows->deleteForPage($page->id);
            $this->pages->deletePermanently($page->id);
            if ($started) { $this->pdo->commit(); }
        } catch (Throwable $exception) {
            if ($started && $this->pdo->inTransaction()) { $this->pdo->rollBack(); }
            throw new RuntimeException('Permanent Page deletion failed and was rolled back.', previous: $exception);
        }
        $this->log($actorId, $actorEmail, 'page.deleted_permanently', $page, null);
        return new PageLifecycleResult(true, 'delete', $page->id, 'archived', null);
    }

    private function log(int $actorId, string $email, string $action, Page $page, ?string $newStatus): void
    {
        $this->audit?->logAction($actorId, $email, $action, 'page', (string) $page->id, $action, [
            'page_id' => $page->id, 'site_id' => $page->siteId,
            'previous_status' => $page->status, 'new_status' => $newStatus,
        ]);
    }
}
