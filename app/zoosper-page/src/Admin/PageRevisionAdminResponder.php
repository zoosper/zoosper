<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Throwable;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Core\Audit\AuditLoggerInterface;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Message\FlashMessageStoreInterface;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Page\Model\Page;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Service\PageRevisionService;

/** Page-owned paginated history, historical preview and restore orchestration. */
final readonly class PageRevisionAdminResponder
{
    private const PAGE_SIZE = 10;

    public function __construct(
        private PageRevisionService $revisions,
        private PageRepository $pages,
        private CsrfTokenManager $csrf,
        private ?FlashMessageStoreInterface $flash = null,
        private ?AuditLoggerInterface $audit = null,
        private ?AdminUrlGenerator $urls = null,
    ) {}

    public function historyHtml(Page $page, int $currentPage = 1): string
    {
        $total = $this->revisions->historyCount($page->id);
        $pageCount = max(1, (int) ceil($total / self::PAGE_SIZE));
        $currentPage = max(1, min($pageCount, $currentPage));
        $rows = '';
        foreach ($this->revisions->historyPage($page->id, $currentPage, self::PAGE_SIZE) as $revision) {
            $preview = $this->url("pages/{$page->id}/revisions/{$revision->id}/preview");
            $restore = $this->url("pages/{$page->id}/revisions/{$revision->id}/restore");
            $rows .= '<tr><td>' . $revision->id . '</td><td>' . $this->e($revision->createdAt)
                . '</td><td>' . $this->e($revision->title) . '</td><td>' . $this->e($revision->status)
                . '</td><td><a target="_blank" rel="noopener" href="' . $this->e($preview) . '">Preview</a> '
                . '<form method="post" action="' . $this->e($restore) . '" class="page-revision-restore-form">'
                . '<input type="hidden" name="_csrf_token" value="' . $this->e($this->csrf->token()) . '">'
                . '<button type="submit">Restore</button>'
                . '</form></td></tr>';
        }
        if ($rows === '') {
            $rows = '<tr><td colspan="5">No revisions captured yet.</td></tr>';
        }

        return '<details class="card page-revision-history"' . ($total > 0 ? '' : ' open') . '>'
            . '<summary><strong>Revision history</strong><span class="muted">' . $total . ' captured</span></summary>'
            . '<div class="page-revision-history__body">'
            . '<p class="muted">Restoring creates a safety snapshot first. Select Restore only after previewing the revision.</p>'
            . '<div class="table-scroll"><table><thead><tr><th>ID</th><th>Created</th><th>Title</th><th>Status</th><th>Actions</th></tr></thead><tbody>'
            . $rows . '</tbody></table></div>'
            . $this->pagination($page, $currentPage, $pageCount, $total)
            . '</div></details>';
    }

    private function pagination(Page $page, int $currentPage, int $pageCount, int $total): string
    {
        if ($pageCount <= 1) {
            return $total === 0 ? '' : '<p class="muted">Showing all ' . $total . ' revisions.</p>';
        }
        $links = '<nav class="page-revision-pagination" aria-label="Revision history pages">';
        if ($currentPage > 1) {
            $links .= '<a class="button secondary" href="' . $this->e($this->editUrl($page, $currentPage - 1)) . '#revision-history">Previous</a>';
        }
        $links .= '<span>Page ' . $currentPage . ' of ' . $pageCount . ' · ' . $total . ' revisions</span>';
        if ($currentPage < $pageCount) {
            $links .= '<a class="button secondary" href="' . $this->e($this->editUrl($page, $currentPage + 1)) . '#revision-history">Next</a>';
        }

        return $links . '</nav>';
    }

    private function editUrl(Page $page, int $revisionPage): string
    {
        $base = $this->url("pages/{$page->id}/edit");
        return $base . (str_contains($base, '?') ? '&' : '?') . 'revision_page=' . $revisionPage;
    }

    public function preview(Page $page, int $revisionId): Response
    {
        try {
            $revision = $this->revisions->revision($page->id, $revisionId);
        } catch (Throwable) {
            return Response::html('<h1>Revision not found</h1>', 404);
        }
        return Response::html('<!doctype html><html lang="en"><head><meta charset="utf-8"><title>'
            . $this->e($revision->title) . ' · Revision preview</title></head><body><aside>Historical revision #'
            . $revision->id . ' · ' . $this->e($revision->createdAt) . '</aside><article><h1>'
            . $this->e($revision->title) . '</h1>' . $revision->content . '</article></body></html>');
    }

    public function restore(Page $page, int $revisionId, AdminUser $actor): Response
    {
        try {
            $revision = $this->revisions->restore($page, $revisionId, $actor->id, $this->pages);
            $this->audit?->logAction(
                $actor->id,
                $actor->email,
                'page.revision_restored',
                'page',
                (string) $page->id,
                'Restored a historical Page revision.',
                ['page_id' => $page->id, 'revision_id' => $revision->id],
            );
            $this->flash?->success('Page revision restored. A safety snapshot of the previous state was retained.', 'page.revision_restored');
        } catch (Throwable $exception) {
            $this->flash?->error($exception->getMessage(), 'page.revision_restore_failed');
        }
        return Response::redirect($this->url("pages/{$page->id}/edit"), 303);
    }

    private function url(string $path): string { return $this->urls?->url($path) ?? '/admin/' . ltrim($path, '/'); }
    private function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}
