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

/** Page-owned Admin history, historical preview and restore orchestration. */
final readonly class PageRevisionAdminResponder
{
    public function __construct(
        private PageRevisionService $revisions,
        private PageRepository $pages,
        private CsrfTokenManager $csrf,
        private ?FlashMessageStoreInterface $flash = null,
        private ?AuditLoggerInterface $audit = null,
        private ?AdminUrlGenerator $urls = null,
    ) {
    }

    public function historyHtml(Page $page): string
    {
        $rows = '';
        foreach ($this->revisions->history($page->id) as $revision) {
            $preview = $this->url("pages/{$page->id}/revisions/{$revision->id}/preview");
            $restore = $this->url("pages/{$page->id}/revisions/{$revision->id}/restore");
            $rows .= '<tr><td>' . $revision->id . '</td><td>' . $this->e($revision->createdAt)
                . '</td><td>' . $this->e($revision->title) . '</td><td>' . $this->e($revision->status)
                . '</td><td><a target="_blank" rel="noopener" href="' . $this->e($preview) . '">Preview</a> '
                . '<form method="post" action="' . $this->e($restore) . '" style="display:inline">'
                . '<input type="hidden" name="_csrf_token" value="' . $this->e($this->csrf->token()) . '">'
                . '<button type="submit" onclick="return confirm(\'Restore this revision? A safety snapshot will be created first.\')">Restore</button>'
                . '</form></td></tr>';
        }
        if ($rows === '') {
            $rows = '<tr><td colspan="5">No revisions captured yet.</td></tr>';
        }
        return '<section class="card page-revision-history"><h2>Revision history</h2>'
            . '<p class="muted">Restoring creates a safety snapshot of the current page first.</p>'
            . '<table><thead><tr><th>ID</th><th>Created</th><th>Title</th><th>Status</th><th>Actions</th></tr></thead><tbody>'
            . $rows . '</tbody></table></section>';
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
