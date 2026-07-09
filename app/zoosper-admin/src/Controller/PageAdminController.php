<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use RuntimeException;
use Zoosper\Auth\Access\Permission;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Page\Model\Page;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Service\PageRenderer;
use Zoosper\Site\Model\Site;
use Zoosper\Site\Repository\SiteRepository;

final readonly class PageAdminController
{
    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private PageRepository $pages,
        private SiteRepository $sites,
        private PageRenderer $renderer,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->requirePageManager();

        if ($user === null) {
            return Response::redirect('/admin/login');
        }

        $rows = '';

        foreach ($this->pages->all() as $page) {
            $status = htmlspecialchars($page->status, ENT_QUOTES, 'UTF-8');
            $title = htmlspecialchars($page->title, ENT_QUOTES, 'UTF-8');
            $slug = htmlspecialchars($page->slug, ENT_QUOTES, 'UTF-8');
            $id = $page->id;
            $publicLink = $page->isPublished() ? '<a href="/' . $slug . '" target="_blank">View</a>' : 'Draft';

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
        {$this->publishButton($page)}
    </td>
</tr>
HTML;
        }

        if ($rows === '') {
            $rows = '<tr><td colspan="5">No pages yet. Create your first page.</td></tr>';
        }

        return Response::html($this->layout('Pages', <<<HTML
<div class="toolbar">
    <a class="button" href="/admin/pages/create">Create page</a>
    <a class="button secondary" href="/admin">Dashboard</a>
</div>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Slug</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>{$rows}</tbody>
</table>
HTML));
    }

    public function createForm(Request $request): Response
    {
        if ($this->requirePageManager() === null) {
            return Response::redirect('/admin/login');
        }

        return Response::html($this->layout('Create page', $this->form(
            action: '/admin/pages/create',
            page: null,
            selectedSiteId: null,
        )));
    }

    public function create(Request $request): Response
    {
        $user = $this->requirePageManager();

        if ($user === null) {
            return Response::redirect('/admin/login');
        }

        $form = $request->form();

        if (!$this->csrf->isValid((string) ($form['_csrf_token'] ?? ''))) {
            return Response::html($this->layout('Create page', $this->form(
                action: '/admin/pages/create',
                page: null,
                selectedSiteId: null,
                error: 'Invalid security token. Please try again.',
            )), 419);
        }

        try {
            $siteId = (int) ($form['site_id'] ?? 0);
            $title = trim((string) ($form['title'] ?? ''));
            $slug = $this->normaliseSlug((string) ($form['slug'] ?? $title));
            $content = (string) ($form['content'] ?? '');
            $status = isset($form['publish']) ? 'published' : 'draft';

            $this->validatePageInput($siteId, $title, $slug);

            $id = $this->pages->create(
                siteId: $siteId,
                title: $title,
                slug: $slug,
                content: $content,
                status: $status,
                userId: $user->id,
            );

            return Response::redirect('/admin/pages/edit?id=' . $id);
        } catch (RuntimeException $exception) {
            return Response::html($this->layout('Create page', $this->form(
                action: '/admin/pages/create',
                page: null,
                selectedSiteId: isset($form['site_id']) ? (int) $form['site_id'] : null,
                error: $exception->getMessage(),
                submitted: $form,
            )), 422);
        }
    }

    public function editForm(Request $request): Response
    {
        if ($this->requirePageManager() === null) {
            return Response::redirect('/admin/login');
        }

        $page = $this->pageFromRequest($request);

        if ($page === null) {
            return Response::html($this->layout('Page not found', '<p>Page not found.</p>'), 404);
        }

        return Response::html($this->layout('Edit page', $this->form(
            action: '/admin/pages/edit?id=' . $page->id,
            page: $page,
            selectedSiteId: $page->siteId,
        )));
    }

    public function update(Request $request): Response
    {
        $user = $this->requirePageManager();

        if ($user === null) {
            return Response::redirect('/admin/login');
        }

        $page = $this->pageFromRequest($request);

        if ($page === null) {
            return Response::html($this->layout('Page not found', '<p>Page not found.</p>'), 404);
        }

        $form = $request->form();

        if (!$this->csrf->isValid((string) ($form['_csrf_token'] ?? ''))) {
            return Response::html($this->layout('Edit page', $this->form(
                action: '/admin/pages/edit?id=' . $page->id,
                page: $page,
                selectedSiteId: $page->siteId,
                error: 'Invalid security token. Please try again.',
            )), 419);
        }

        try {
            $siteId = (int) ($form['site_id'] ?? 0);
            $title = trim((string) ($form['title'] ?? ''));
            $slug = $this->normaliseSlug((string) ($form['slug'] ?? $title));
            $content = (string) ($form['content'] ?? '');

            $this->validatePageInput($siteId, $title, $slug);

            $this->pages->update(
                id: $page->id,
                siteId: $siteId,
                title: $title,
                slug: $slug,
                content: $content,
                userId: $user->id,
            );

            if (isset($form['publish'])) {
                $this->pages->publish($page->id, $user->id);
            }

            return Response::redirect('/admin/pages/edit?id=' . $page->id);
        } catch (RuntimeException $exception) {
            return Response::html($this->layout('Edit page', $this->form(
                action: '/admin/pages/edit?id=' . $page->id,
                page: $page,
                selectedSiteId: isset($form['site_id']) ? (int) $form['site_id'] : $page->siteId,
                error: $exception->getMessage(),
                submitted: $form,
            )), 422);
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
        return $this->changeStatus($request, publish: true);
    }

    public function unpublish(Request $request): Response
    {
        return $this->changeStatus($request, publish: false);
    }

    private function changeStatus(Request $request, bool $publish): Response
    {
        $user = $this->requirePageManager();

        if ($user === null) {
            return Response::redirect('/admin/login');
        }

        $form = $request->form();

        if (!$this->csrf->isValid((string) ($form['_csrf_token'] ?? ''))) {
            return Response::html($this->layout('Invalid token', '<p>Invalid security token.</p>'), 419);
        }

        $page = $this->pageFromRequest($request);

        if ($page === null) {
            return Response::html($this->layout('Page not found', '<p>Page not found.</p>'), 404);
        }

        if ($publish) {
            $this->pages->publish($page->id, $user->id);
        } else {
            $this->pages->unpublish($page->id, $user->id);
        }

        return Response::redirect('/admin/pages');
    }

    private function requirePageManager(): ?AdminUser
    {
        return $this->guard->requirePermission(Permission::PageManage->value);
    }

    private function pageFromRequest(Request $request): ?Page
    {
        $id = $request->query('id');

        if ($id === null || !ctype_digit($id)) {
            return null;
        }

        return $this->pages->findById((int) $id);
    }

    /**
     * @param array<string, mixed> $submitted
     */
    private function form(
        string $action,
        ?Page $page,
        ?int $selectedSiteId,
        ?string $error = null,
        array $submitted = [],
    ): string {
        $token = htmlspecialchars($this->csrf->token(), ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars((string) ($submitted['title'] ?? $page?->title ?? ''), ENT_QUOTES, 'UTF-8');
        $slug = htmlspecialchars((string) ($submitted['slug'] ?? $page?->slug ?? ''), ENT_QUOTES, 'UTF-8');
        $content = htmlspecialchars((string) ($submitted['content'] ?? $page?->content ?? ''), ENT_QUOTES, 'UTF-8');
        $errorHtml = $error !== null
            ? '<p class="error">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</p>'
            : '';
        $siteOptions = $this->siteOptions($selectedSiteId);
        $publishChecked = ($submitted !== [] && isset($submitted['publish'])) || $page?->isPublished()
            ? ' checked'
            : '';

        return <<<HTML
{$errorHtml}
<form method="post" action="{$action}" class="page-form">
    <input type="hidden" name="_csrf_token" value="{$token}">

    <label>
        Site
        <select name="site_id" required>{$siteOptions}</select>
    </label>

    <label>
        Title
        <input type="text" name="title" value="{$title}" required>
    </label>

    <label>
        Slug
        <input type="text" name="slug" value="{$slug}" required>
    </label>

    <label>
        Content
        <textarea name="content" rows="14" required>{$content}</textarea>
    </label>

    <label class="checkbox">
        <input type="checkbox" name="publish" value="1"{$publishChecked}>
        Publish page
    </label>

    <div class="toolbar">
        <button type="submit">Save page</button>
        <a class="button secondary" href="/admin/pages">Back to pages</a>
    </div>
</form>
HTML;
    }

    private function siteOptions(?int $selectedSiteId): string
    {
        $html = '';

        foreach ($this->sites->allActive() as $site) {
            $selected = $site->id === $selectedSiteId ? ' selected' : '';
            $label = htmlspecialchars($site->name . ' (' . $site->code . ')', ENT_QUOTES, 'UTF-8');
            $html .= '<option value="' . $site->id . '"' . $selected . '>' . $label . '</option>';
        }

        return $html;
    }

    private function publishButton(Page $page): string
    {
        $token = htmlspecialchars($this->csrf->token(), ENT_QUOTES, 'UTF-8');
        $id = $page->id;

        if ($page->isPublished()) {
            return <<<HTML
<form method="post" action="/admin/pages/unpublish?id={$id}" class="inline-form">
    <input type="hidden" name="_csrf_token" value="{$token}">
    <button type="submit">Unpublish</button>
</form>
HTML;
        }

        return <<<HTML
<form method="post" action="/admin/pages/publish?id={$id}" class="inline-form">
    <input type="hidden" name="_csrf_token" value="{$token}">
    <button type="submit">Publish</button>
</form>
HTML;
    }

    private function validatePageInput(int $siteId, string $title, string $slug): void
    {
        if ($siteId <= 0 || $this->sites->findById($siteId) === null) {
            throw new RuntimeException('Please choose a valid site.');
        }

        if ($title === '') {
            throw new RuntimeException('Title is required.');
        }

        if ($slug === '') {
            throw new RuntimeException('Slug is required.');
        }
    }

    private function normaliseSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug) ?: '';

        return trim($slug, '-');
    }

    private function layout(string $title, string $content): string
    {
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$safeTitle} - Zoosper Admin</title>
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: 0;
            background: #f8fafc;
            color: #102a43;
        }
        header, main {
            max-width: 1100px;
            margin: 0 auto;
            padding: 1.5rem;
        }
        header {
            border-bottom: 1px solid #d9e2ec;
            background: white;
        }
        a { color: #0f766e; }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            border-bottom: 1px solid #e2e8f0;
            padding: .75rem;
            text-align: left;
            vertical-align: top;
        }
        input, textarea, select {
            box-sizing: border-box;
            width: 100%;
            padding: .7rem;
            margin-top: .25rem;
            border: 1px solid #cbd5e1;
            border-radius: .5rem;
            font: inherit;
        }
        label {
            display: block;
            margin: 1rem 0;
        }
        button, .button {
            display: inline-block;
            padding: .65rem .9rem;
            border: 0;
            border-radius: .5rem;
            background: #0f766e;
            color: white;
            text-decoration: none;
            cursor: pointer;
        }
        .secondary {
            background: #475569;
        }
        .toolbar {
            display: flex;
            gap: .75rem;
            align-items: center;
            margin: 1rem 0;
        }
        .actions {
            display: flex;
            gap: .6rem;
            align-items: center;
            flex-wrap: wrap;
        }
        .inline-form {
            display: inline;
        }
        .inline-form button {
            padding: .35rem .65rem;
            background: #334155;
        }
        .error {
            padding: .75rem;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #991b1b;
            border-radius: .5rem;
        }
        .page-form {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.25rem;
        }
        .checkbox {
            display: flex;
            gap: .5rem;
            align-items: center;
        }
        .checkbox input {
            width: auto;
        }
    </style>
</head>
<body>
    <header>
        <strong>Zoosper Admin</strong>
        <nav>
            <a href="/admin">Dashboard</a> ·
            <a href="/admin/pages">Pages</a>
        </nav>
    </header>
    <main>
        <h1>{$safeTitle}</h1>
        {$content}
    </main>
</body>
</html>
HTML;
    }
}
