<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\Lifecycle;

use Throwable;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Message\FlashMessageStoreInterface;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Page\Lifecycle\PageLifecycleCoordinator;
use Zoosper\Page\Lifecycle\PageLifecycleResult;
use Zoosper\Page\Model\Page;

/** HTTP-neutral Page lifecycle presentation and operation responder. */
final readonly class PageLifecycleAdminResponder
{
    public function __construct(
        private PageLifecycleCoordinator $lifecycle,
        private CsrfTokenManager $csrf,
        private ?FlashMessageStoreInterface $flash = null,
        private ?AdminUrlGenerator $urls = null,
    ) {}

    public function actionsHtml(Page $page): string
    {
        $token = $this->e($this->csrf->token());
        $id = $page->id;
        if ($page->status === 'archived') {
            return '<section class="card page-lifecycle-actions" aria-labelledby="page-lifecycle-heading">'
                . '<h2 id="page-lifecycle-heading">Page lifecycle</h2>'
                . '<p class="muted">Restore this Page to draft, or permanently delete it after all references are removed.</p>'
                . $this->form("pages/{$id}/restore", $token, 'Restore to draft', 'button secondary')
                . $this->form("pages/{$id}/delete", $token, 'Delete permanently', 'button button--danger', true)
                . '</section>';
        }

        return '<section class="card page-lifecycle-actions" aria-labelledby="page-lifecycle-heading">'
            . '<h2 id="page-lifecycle-heading">Page lifecycle</h2>'
            . '<p class="muted">Archiving removes this Page from published resolution while retaining its content and revision history.</p>'
            . $this->form("pages/{$id}/archive", $token, 'Archive page', 'button secondary')
            . '</section>';
    }

    public function archive(Page $page, AdminUser $actor): Response
    {
        return $this->operate('archive', $page, $actor);
    }

    public function restore(Page $page, AdminUser $actor): Response
    {
        return $this->operate('restore', $page, $actor);
    }

    public function delete(Page $page, AdminUser $actor): Response
    {
        return $this->operate('delete', $page, $actor);
    }

    private function operate(string $operation, Page $page, AdminUser $actor): Response
    {
        try {
            $result = match ($operation) {
                'archive' => $this->lifecycle->archive($page, $actor->id, $actor->email),
                'restore' => $this->lifecycle->restore($page, $actor->id, $actor->email),
                'delete' => $this->lifecycle->deletePermanently($page, $actor->id, $actor->email),
                default => throw new \LogicException('Unsupported Page lifecycle operation.'),
            };
            $this->present($result);
        } catch (Throwable $exception) {
            $this->flash?->error($exception->getMessage(), 'page.lifecycle.failed');
            return Response::redirect($this->url("pages/{$page->id}/edit"), 303);
        }

        return Response::redirect(
            $operation === 'delete' && $result->successful
                ? $this->url('pages')
                : $this->url("pages/{$page->id}/edit"),
            303,
        );
    }

    private function present(PageLifecycleResult $result): void
    {
        if ($result->successful) {
            $message = match ($result->operation) {
                'archive' => 'Page archived. A safety revision was retained.',
                'restore' => 'Page restored to draft. A safety revision was retained.',
                'delete' => 'Page permanently deleted.',
                default => 'Page lifecycle operation completed.',
            };
            $this->flash?->success($message, 'page.lifecycle.' . $result->operation);
            return;
        }

        $details = [];
        if (($result->blockers['menu_items'] ?? 0) > 0) {
            $details[] = $result->blockers['menu_items'] . ' Menu item reference(s)';
        }
        if (($result->blockers['url_rewrites'] ?? 0) > 0) {
            $details[] = $result->blockers['url_rewrites'] . ' URL Rewrite reference(s)';
        }
        $message = $result->message ?? 'Page lifecycle operation was blocked.';
        if ($details !== []) { $message .= ' Blocking references: ' . implode(', ', $details) . '.'; }
        $this->flash?->error($message, 'page.lifecycle.blocked');
    }

    private function form(string $path, string $token, string $label, string $class, bool $destructive = false): string
    {
        $description = $destructive
            ? '<p class="muted">This cannot be undone. Deletion remains blocked while Menu items or Page URL Rewrites reference this Page.</p>'
            : '';
        return '<form method="post" action="' . $this->e($this->url($path)) . '" class="page-lifecycle-form">'
            . '<input type="hidden" name="_csrf_token" value="' . $token . '">'
            . $description
            . '<button type="submit" class="' . $this->e($class) . '">' . $this->e($label) . '</button>'
            . '</form>';
    }

    private function url(string $path): string { return $this->urls?->url($path) ?? '/admin/' . ltrim($path, '/'); }
    private function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}
