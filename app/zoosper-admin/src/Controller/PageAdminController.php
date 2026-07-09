<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use RuntimeException;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Access\Permission;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Page\Model\Page;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Service\PageRenderer;
use Zoosper\Site\Repository\SiteRepository;

final readonly class PageAdminController
{
    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private PageRepository $pages,
        private SiteRepository $sites,
        private PageRenderer $renderer,
        private AdminLayout $layout,
    ) {
    }

    public function index(Request $request): Response
    {
        if ($this->requirePageManager() === null) {
            return Response::redirect('/admin/login');
        }

        $rows = '';
        foreach ($this->pages->all() as $page) {
            $id = $page->id;
            $title = $this->e($page->title);
            $slug = $this->e($page->slug);
            $status = $this->e($page->status);
            $publicLink = $page->isPublished() ? '<a href="/' . $slug . '" target="_blank">View</a>' : '<span class="muted">Draft</span>';
            $rows .= <<<HTML
<tr>
    <td>{$id}</td>
    <td>{$title}</td>
    <td><code>/{$slug}</code></td>
    <td>{$status}</td>
    <td class="actions">
        <a href="/admin/pages/edit?id={$id}">Edit</a>
        <a href="/admin/pages/preview?id={$id}" target="_blank">Preview</a>
        {$publicLink}
        {$this->statusButton($page)}
    </td>
</tr>
HTML;
        }

        if ($rows === '') {
            $rows = '<tr><td colspan="5">No pages yet.</td></tr>';
        }

        return $this->html('Pages', <<<HTML
<div class="toolbar">
    <a class="button" href="/admin/pages/create">Create page</a>
</div>
<table>
    <thead><tr><th>ID</th><th>Title</th><th>Slug</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>{$rows}</tbody>
</table>
HTML);
    }

    public function createForm(Request $request): Response
    {
        if ($this->requirePageManager() === null) {
            return Response::redirect('/admin/login');
        }

        return $this->html('Create page', $this->form('/admin/pages/create'));
    }

    public function create(Request $request): Response
    {
        $user = $this->requirePageManager();
        if ($user === null) {
            return Response::redirect('/admin/login');
        }

        $form = $request->form();
        if (!$this->csrf->isValid((string) ($form['_csrf_token'] ?? ''))) {
            return $this->html('Create page', $this->form('/admin/pages/create', error: 'Invalid security token.', submitted: $form), 419);
        }

        try {
            $id = $this->pages->create(
                siteId: (int) ($form['site_id'] ?? 0),
                title: trim((string) ($form['title'] ?? '')),
                slug: $this->normaliseSlug((string) ($form['slug'] ?? '')),
                content: (string) ($form['content'] ?? ''),
                status: isset($form['publish']) ? 'published' : 'draft',
                userId: $user->id,
            );
            return Response::redirect('/admin/pages/edit?id=' . $id);
        } catch (RuntimeException $exception) {
            return $this->html('Create page', $this->form('/admin/pages/create', error: $exception->getMessage(), submitted: $form), 422);
        }
    }

    public function editForm(Request $request): Response
    {
        if ($this->requirePageManager() === null) {
            return Response::redirect('/admin/login');
        }

        $page = $this->pageFromRequest($request);
        if ($page === null) {
            return $this->html('Page not found', '<p>Page not found.</p>', 404);
        }

        return $this->html('Edit page', $this->form('/admin/pages/edit?id=' . $page->id, $page));
    }

    public function update(Request $request): Response
    {
        $user = $this->requirePageManager();
        if ($user === null) {
            return Response::redirect('/admin/login');
        }

        $page = $this->pageFromRequest($request);
        if ($page === null) {
            return $this->html('Page not found', '<p>Page not found.</p>', 404);
        }

        $form = $request->form();
        if (!$this->csrf->isValid((string) ($form['_csrf_token'] ?? ''))) {
            return $this->html('Edit page', $this->form('/admin/pages/edit?id=' . $page->id, $page, 'Invalid security token.', $form), 419);
        }

        try {
            $this->pages->update(
                id: $page->id,
                siteId: (int) ($form['site_id'] ?? 0),
                title: trim((string) ($form['title'] ?? '')),
                slug: $this->normaliseSlug((string) ($form['slug'] ?? '')),
                content: (string) ($form['content'] ?? ''),
                userId: $user->id,
            );
            if (isset($form['publish'])) {
                $this->pages->publish($page->id, $user->id);
            }
            return Response::redirect('/admin/pages/edit?id=' . $page->id);
        } catch (RuntimeException $exception) {
            return $this->html('Edit page', $this->form('/admin/pages/edit?id=' . $page->id, $page, $exception->getMessage(), $form), 422);
        }
    }

    public function preview(Request $request): Response
    {
        if ($this->requirePageManager() === null) {
            return Response::redirect('/admin/login');
        }

        $page = $this->pageFromRequest($request);
        if ($page === null) {
            return Response::html('<h1>Page not found</h1>', 404);
        }

        $site = $this->sites->findById($page->siteId);
        if ($site === null) {
            return Response::html('<h1>Site not found</h1>', 404);
        }

        return Response::html($this->renderer->render($page, $site));
    }

    public function publish(Request $request): Response
    {
        return $this->changeStatus($request, true);
    }

    public function unpublish(Request $request): Response
    {
        return $this->changeStatus($request, false);
    }

    private function changeStatus(Request $request, bool $publish): Response
    {
        $user = $this->requirePageManager();
        if ($user === null) {
            return Response::redirect('/admin/login');
        }

        if (!$this->csrf->isValid((string) ($request->form()['_csrf_token'] ?? ''))) {
            return $this->html('Invalid token', '<p>Invalid security token.</p>', 419);
        }

        $page = $this->pageFromRequest($request);
        if ($page === null) {
            return $this->html('Page not found', '<p>Page not found.</p>', 404);
        }

        $publish ? $this->pages->publish($page->id, $user->id) : $this->pages->unpublish($page->id, $user->id);
        return Response::redirect('/admin/pages');
    }

    private function requirePageManager(): ?AdminUser
    {
        return $this->guard->requirePermission(Permission::PageManage->value);
    }

    private function pageFromRequest(Request $request): ?Page
    {
        $id = $request->query('id');
        return $id !== null && ctype_digit($id) ? $this->pages->findById((int) $id) : null;
    }

    /** @param array<string, mixed> $submitted */
    private function form(string $action, ?Page $page = null, ?string $error = null, array $submitted = []): string
    {
        $token = $this->e($this->csrf->token());
        $siteId = (int) ($submitted['site_id'] ?? $page?->siteId ?? 0);
        $title = $this->e((string) ($submitted['title'] ?? $page?->title ?? ''));
        $slug = $this->e((string) ($submitted['slug'] ?? $page?->slug ?? ''));
        $content = $this->e((string) ($submitted['content'] ?? $page?->content ?? ''));
        $siteOptions = $this->siteOptions($siteId);
        $publishChecked = (isset($submitted['publish']) || $page?->isPublished()) ? ' checked' : '';
        $errorHtml = $error !== null ? '<p class="error">' . $this->e($error) . '</p>' : '';

        return <<<HTML
{$errorHtml}
<form method="post" action="{$action}" class="page-form">
    <input type="hidden" name="_csrf_token" value="{$token}">
    <label>Site <select name="site_id" required>{$siteOptions}</select></label>
    <label>Title <input type="text" name="title" value="{$title}" required></label>
    <label>Slug <input type="text" name="slug" value="{$slug}" required></label>
    <label>Content <textarea name="content" rows="14" required>{$content}</textarea></label>
    <label class="checkbox"><input type="checkbox" name="publish" value="1"{$publishChecked}> Publish page</label>
    <div class="toolbar"><button type="submit">Save page</button><a class="button secondary" href="/admin/pages">Back</a></div>
</form>
HTML;
    }

    private function siteOptions(int $selectedSiteId): string
    {
        $html = '';
        foreach ($this->sites->allActive() as $site) {
            $selected = $site->id === $selectedSiteId ? ' selected' : '';
            $html .= '<option value="' . $site->id . '"' . $selected . '>' . $this->e($site->name . ' (' . $site->code . ')') . '</option>';
        }
        return $html;
    }

    private function statusButton(Page $page): string
    {
        $token = $this->e($this->csrf->token());
        $action = $page->isPublished() ? 'unpublish' : 'publish';
        $label = $page->isPublished() ? 'Unpublish' : 'Publish';
        return '<form method="post" action="/admin/pages/' . $action . '?id=' . $page->id . '" class="inline-form"><input type="hidden" name="_csrf_token" value="' . $token . '"><button type="submit">' . $label . '</button></form>';
    }

    private function normaliseSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug) ?: '';
        return trim($slug, '-');
    }

    private function html(string $title, string $content, int $statusCode = 200): Response
    {
        return Response::html($this->layout->render($title, $content, $this->guard->user(), 'pages'), $statusCode);
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
