<?php

declare(strict_types=1);

namespace Zoosper\Site\Admin\Controller;

use RuntimeException;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Site\Model\Site;
use Zoosper\Site\Repository\SiteRepository;

/**
 * Launch-readiness admin CRUD for configured CMS sites.
 *
 * This controller follows the existing admin controller convention: middleware
 * owns authentication/permission checks, while the controller handles request
 * orchestration, validation, repository calls and safe admin HTML rendering.
 */
final readonly class SiteAdminController
{
    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private SiteRepository $sites,
        private AdminLayout $layout,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->currentAdminUser();
        $rows = $this->sites->all();

        $html = '<section class="card">'
            . '<div class="admin-page-heading"><h2>Sites</h2><a class="button" href="/admin/sites/create">Create site</a></div>';

        if ($rows === []) {
            $html .= '<p class="muted">No sites exist yet. Create your first site to start publishing.</p>';
        } else {
            $html .= '<table class="admin-table"><thead><tr>'
                . '<th>ID</th><th>Name</th><th>Code</th><th>Status</th><th>Locale</th><th>Theme</th><th></th>'
                . '</tr></thead><tbody>';
            foreach ($rows as $site) {
                $html .= '<tr>'
                    . '<td>' . $site->id . '</td>'
                    . '<td>' . $this->e($site->name) . '</td>'
                    . '<td><code>' . $this->e($site->code) . '</code></td>'
                    . '<td>' . $this->e($site->status) . '</td>'
                    . '<td>' . $this->e($site->locale) . '</td>'
                    . '<td><code>' . $this->e($site->themeCode) . '</code></td>'
                    . '<td><a href="/admin/sites/edit?id=' . $site->id . '">Edit</a></td>'
                    . '</tr>';
            }
            $html .= '</tbody></table>';
        }

        $html .= '</section>';

        return $this->html('Sites', $html, $user);
    }

    public function create(Request $request): Response
    {
        $user = $this->currentAdminUser();

        return $this->html('Create site', $this->form('/admin/sites/create'), $user);
    }

    public function store(Request $request): Response
    {
        $user = $this->currentAdminUser();
        $form = $request->form();

        try {
            $this->sites->create(
                code: $this->requiredSlug($form, 'code', 'Site code is required.'),
                name: $this->requiredString($form, 'name', 'Site name is required.'),
                host: $this->requiredHost($form, 'host', 'Primary host is required.'),
                homepageSlug: $this->optionalSlug($form, 'homepage_slug', 'home'),
                themeCode: $this->optionalSlug($form, 'theme_code', 'default'),
                locale: $this->optionalString($form, 'locale', 'en_AU'),
                currency: $this->optionalString($form, 'currency', 'AUD'),
                baseUrl: $this->optionalString($form, 'base_url', ''),
                websiteCode: $this->optionalSlug($form, 'website_code', 'main'),
                storeCode: $this->optionalSlug($form, 'store_code', 'main'),
                storeViewCode: $this->optionalSlug($form, 'store_view_code', 'default'),
                pathPrefix: $this->normalisePathPrefix((string) ($form['path_prefix'] ?? '')),
            );

            return Response::redirect('/admin/sites');
        } catch (RuntimeException $exception) {
            return $this->html('Create site', $this->form('/admin/sites/create', null, $exception->getMessage(), $form), $user, 422);
        }
    }

    public function edit(Request $request): Response
    {
        $user = $this->currentAdminUser();
        $site = $this->siteFromRequest($request);
        if ($site === null) {
            return $this->html('Site not found', '<section class="card"><p class="error">Site not found.</p></section>', $user, 404);
        }

        return $this->html('Edit site', $this->form('/admin/sites/edit?id=' . $site->id, $site), $user);
    }

    public function update(Request $request): Response
    {
        $user = $this->currentAdminUser();
        $site = $this->siteFromRequest($request);
        if ($site === null) {
            return $this->html('Site not found', '<section class="card"><p class="error">Site not found.</p></section>', $user, 404);
        }

        $form = $request->form();
        try {
            $this->sites->update(
                id: $site->id,
                code: $this->requiredSlug($form, 'code', 'Site code is required.'),
                name: $this->requiredString($form, 'name', 'Site name is required.'),
                status: $this->normaliseStatus((string) ($form['status'] ?? 'active')),
                homepageSlug: $this->optionalSlug($form, 'homepage_slug', 'home'),
                themeCode: $this->optionalSlug($form, 'theme_code', 'default'),
                locale: $this->optionalString($form, 'locale', 'en_AU'),
                currency: $this->optionalString($form, 'currency', 'AUD'),
                baseUrl: $this->optionalString($form, 'base_url', ''),
                websiteCode: $this->optionalSlug($form, 'website_code', 'main'),
                storeCode: $this->optionalSlug($form, 'store_code', 'main'),
                storeViewCode: $this->optionalSlug($form, 'store_view_code', 'default'),
                pathPrefix: $this->normalisePathPrefix((string) ($form['path_prefix'] ?? '')),
            );

            return Response::redirect('/admin/sites');
        } catch (RuntimeException $exception) {
            return $this->html('Edit site', $this->form('/admin/sites/edit?id=' . $site->id, $site, $exception->getMessage(), $form), $user, 422);
        }
    }

    /** @param array<string, mixed> $submitted */
    private function form(string $action, ?Site $site = null, ?string $error = null, array $submitted = []): string
    {
        $value = static fn (string $key, mixed $fallback = ''): string => htmlspecialchars((string) ($submitted[$key] ?? $fallback), ENT_QUOTES, 'UTF-8');
        $status = (string) ($submitted['status'] ?? $site?->status ?? 'active');
        $errorHtml = $error !== null ? '<p class="error">' . $this->e($error) . '</p>' : '';

        return '<section class="card"><h2>' . ($site === null ? 'Create site' : 'Edit site') . '</h2>'
            . $errorHtml
            . '<form method="post" action="' . $this->e($action) . '">'
            . '<input type="hidden" name="_csrf_token" value="' . $this->e($this->csrf->token()) . '">'
            . $this->field('Name', 'name', $value('name', $site?->name ?? ''))
            . $this->field('Code', 'code', $value('code', $site?->code ?? ''))
            . $this->field('Primary host', 'host', $value('host', ''))
            . $this->select('Status', 'status', ['active' => 'Active', 'inactive' => 'Inactive'], $status)
            . $this->field('Homepage slug', 'homepage_slug', $value('homepage_slug', $site?->homepageSlug ?? 'home'))
            . $this->field('Theme code', 'theme_code', $value('theme_code', $site?->themeCode ?? 'default'))
            . $this->field('Locale', 'locale', $value('locale', $site?->locale ?? 'en_AU'))
            . $this->field('Currency', 'currency', $value('currency', $site?->currency ?? 'AUD'))
            . $this->field('Base URL', 'base_url', $value('base_url', $site?->baseUrl ?? ''))
            . $this->field('Website code', 'website_code', $value('website_code', $site?->websiteCode ?? 'main'))
            . $this->field('Store code', 'store_code', $value('store_code', $site?->storeCode ?? 'main'))
            . $this->field('Store view code', 'store_view_code', $value('store_view_code', $site?->storeViewCode ?? 'default'))
            . $this->field('Path prefix', 'path_prefix', $value('path_prefix', $site?->pathPrefix ?? ''))
            . '<p><button type="submit">Save site</button> <a href="/admin/sites">Cancel</a></p>'
            . '</form></section>';
    }

    private function siteFromRequest(Request $request): ?Site
    {
        $id = $request->query('id');

        return $id !== null && ctype_digit($id) ? $this->sites->findById((int) $id) : null;
    }

    private function html(string $title, string $content, AdminUser $user, int $statusCode = 200): Response
    {
        return Response::html($this->layout->render($title, $content, $user, 'sites'), $statusCode);
    }

    private function field(string $label, string $name, string $value): string
    {
        return '<label>' . $this->e($label) . '<input type="text" name="' . $this->e($name) . '" value="' . $value . '"></label>';
    }

    /** @param array<string, string> $options */
    private function select(string $label, string $name, array $options, string $selected): string
    {
        $html = '<label>' . $this->e($label) . '<select name="' . $this->e($name) . '">';
        foreach ($options as $value => $text) {
            $html .= '<option value="' . $this->e($value) . '"' . ($value === $selected ? ' selected' : '') . '>' . $this->e($text) . '</option>';
        }

        return $html . '</select></label>';
    }

    private function currentAdminUser(): AdminUser
    {
        $user = $this->guard->user();
        if ($user === null) {
            throw new RuntimeException('Authenticated admin user required after middleware guard.');
        }

        return $user;
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /** @param array<string, mixed> $form */
    private function requiredString(array $form, string $key, string $message): string
    {
        $value = trim((string) ($form[$key] ?? ''));
        if ($value === '') {
            throw new RuntimeException($message);
        }

        return $value;
    }

    /** @param array<string, mixed> $form */
    private function requiredSlug(array $form, string $key, string $message): string
    {
        $value = $this->requiredString($form, $key, $message);
        if (!preg_match('/^[a-z0-9][a-z0-9_-]*$/i', $value)) {
            throw new RuntimeException('Invalid ' . str_replace('_', ' ', $key) . '. Use letters, numbers, underscore or dash.');
        }

        return $value;
    }

    /** @param array<string, mixed> $form */
    private function optionalSlug(array $form, string $key, string $default): string
    {
        $value = trim((string) ($form[$key] ?? ''));
        if ($value === '') {
            return $default;
        }
        if (!preg_match('/^[a-z0-9][a-z0-9_-]*$/i', $value)) {
            throw new RuntimeException('Invalid ' . str_replace('_', ' ', $key) . '. Use letters, numbers, underscore or dash.');
        }

        return $value;
    }

    /** @param array<string, mixed> $form */
    private function optionalString(array $form, string $key, string $default): string
    {
        $value = trim((string) ($form[$key] ?? ''));

        return $value !== '' ? $value : $default;
    }

    /** @param array<string, mixed> $form */
    private function requiredHost(array $form, string $key, string $message): string
    {
        $host = strtolower(trim((string) ($form[$key] ?? '')));
        if ($host === '') {
            throw new RuntimeException($message);
        }
        if (!preg_match('/^[a-z0-9.-]+$/', $host)) {
            throw new RuntimeException('Invalid host.');
        }

        return $host;
    }

    private function normaliseStatus(string $status): string
    {
        return in_array($status, ['active', 'inactive'], true) ? $status : 'inactive';
    }

    private function normalisePathPrefix(string $pathPrefix): string
    {
        $pathPrefix = trim($pathPrefix);
        if ($pathPrefix === '') {
            return '';
        }

        return '/' . trim($pathPrefix, '/');
    }
}
