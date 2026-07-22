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
use Zoosper\Site\Model\SiteDomain;
use Zoosper\Site\Repository\SiteDomainRepository;
use Zoosper\Site\Repository\SiteRepository;

/** Launch-readiness admin CRUD for site domain mappings. */
final readonly class SiteDomainAdminController
{
    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private SiteDomainRepository $domains,
        private SiteRepository $sites,
        private AdminLayout $layout,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->currentAdminUser();
        $domains = $this->domains->all();
        $siteNames = $this->siteNames();

        $html = '<section class="card"><div class="admin-page-heading"><h2>Site Domains</h2><a class="button" href="/admin/site-domains/create">Add domain</a></div>';
        if ($domains === []) {
            $html .= '<p class="muted">No site domains exist yet. Add a domain to route requests to a site.</p>';
        } else {
            $html .= '<table class="admin-table"><thead><tr><th>ID</th><th>Host</th><th>Site</th><th>Primary</th><th></th></tr></thead><tbody>';
            foreach ($domains as $domain) {
                $html .= '<tr>'
                    . '<td>' . $domain->id . '</td>'
                    . '<td><code>' . $this->e($domain->host) . '</code></td>'
                    . '<td>' . $this->e($siteNames[$domain->siteId] ?? ('#' . $domain->siteId)) . '</td>'
                    . '<td>' . ($domain->isPrimary ? 'Yes' : 'No') . '</td>'
                    . '<td><a href="/admin/site-domains/edit?id=' . $domain->id . '">Edit</a></td>'
                    . '</tr>';
            }
            $html .= '</tbody></table>';
        }
        $html .= '</section>';

        return $this->html('Site Domains', $html, $user);
    }

    public function create(Request $request): Response
    {
        $user = $this->currentAdminUser();

        return $this->html('Add site domain', $this->form('/admin/site-domains/create'), $user);
    }

    public function store(Request $request): Response
    {
        $user = $this->currentAdminUser();
        $form = $request->form();

        try {
            $this->domains->create(
                siteId: $this->requiredSiteId($form),
                host: $this->requiredHost($form),
                isPrimary: isset($form['is_primary']),
            );

            return Response::redirect('/admin/site-domains');
        } catch (RuntimeException $exception) {
            return $this->html('Add site domain', $this->form('/admin/site-domains/create', null, $exception->getMessage(), $form), $user, 422);
        }
    }

    public function edit(Request $request): Response
    {
        $user = $this->currentAdminUser();
        $domain = $this->domainFromRequest($request);
        if ($domain === null) {
            return $this->html('Domain not found', '<section class="card"><p class="error">Site domain not found.</p></section>', $user, 404);
        }

        return $this->html('Edit site domain', $this->form('/admin/site-domains/edit?id=' . $domain->id, $domain), $user);
    }

    public function update(Request $request): Response
    {
        $user = $this->currentAdminUser();
        $domain = $this->domainFromRequest($request);
        if ($domain === null) {
            return $this->html('Domain not found', '<section class="card"><p class="error">Site domain not found.</p></section>', $user, 404);
        }

        $form = $request->form();
        try {
            $this->domains->update(
                id: $domain->id,
                siteId: $this->requiredSiteId($form),
                host: $this->requiredHost($form),
                isPrimary: isset($form['is_primary']),
            );

            return Response::redirect('/admin/site-domains');
        } catch (RuntimeException $exception) {
            return $this->html('Edit site domain', $this->form('/admin/site-domains/edit?id=' . $domain->id, $domain, $exception->getMessage(), $form), $user, 422);
        }
    }

    /** @param array<string, mixed> $submitted */
    private function form(string $action, ?SiteDomain $domain = null, ?string $error = null, array $submitted = []): string
    {
        $selectedSiteId = (int) ($submitted['site_id'] ?? $domain?->siteId ?? 0);
        $host = $this->e((string) ($submitted['host'] ?? $domain?->host ?? ''));
        $checked = (isset($submitted['is_primary']) || $domain?->isPrimary === true) ? ' checked' : '';
        $errorHtml = $error !== null ? '<p class="error">' . $this->e($error) . '</p>' : '';

        return '<section class="card"><h2>' . ($domain === null ? 'Add site domain' : 'Edit site domain') . '</h2>'
            . $errorHtml
            . '<form method="post" action="' . $this->e($action) . '">'
            . '<input type="hidden" name="_csrf_token" value="' . $this->e($this->csrf->token()) . '">'
            . '<label>Site<select name="site_id">' . $this->siteOptions($selectedSiteId) . '</select></label>'
            . '<label>Host<input type="text" name="host" value="' . $host . '"></label>'
            . '<label><input type="checkbox" name="is_primary" value="1"' . $checked . '> Primary domain</label>'
            . '<p><button type="submit">Save domain</button> <a href="/admin/site-domains">Cancel</a></p>'
            . '</form></section>';
    }

    private function domainFromRequest(Request $request): ?SiteDomain
    {
        $id = $request->query('id');

        return $id !== null && ctype_digit($id) ? $this->domains->findById((int) $id) : null;
    }

    private function html(string $title, string $content, AdminUser $user, int $statusCode = 200): Response
    {
        return Response::html($this->layout->render($title, $content, $user, 'site-domains'), $statusCode);
    }

    private function currentAdminUser(): AdminUser
    {
        $user = $this->guard->user();
        if ($user === null) {
            throw new RuntimeException('Authenticated admin user required after middleware guard.');
        }

        return $user;
    }

    private function siteOptions(int $selectedSiteId): string
    {
        $html = '';
        foreach ($this->sites->all() as $site) {
            $html .= '<option value="' . $site->id . '"' . ($site->id === $selectedSiteId ? ' selected' : '') . '>' . $this->e($site->name . ' (' . $site->code . ')') . '</option>';
        }

        return $html;
    }

    /** @return array<int, string> */
    private function siteNames(): array
    {
        $names = [];
        foreach ($this->sites->all() as $site) {
            $names[$site->id] = $site->name;
        }

        return $names;
    }

    /** @param array<string, mixed> $form */
    private function requiredSiteId(array $form): int
    {
        $siteId = (int) ($form['site_id'] ?? 0);
        if ($siteId <= 0 || $this->sites->findById($siteId) === null) {
            throw new RuntimeException('Valid site is required.');
        }

        return $siteId;
    }

    /** @param array<string, mixed> $form */
    private function requiredHost(array $form): string
    {
        $host = strtolower(trim((string) ($form['host'] ?? '')));
        if ($host === '' || !preg_match('/^[a-z0-9.-]+$/', $host)) {
            throw new RuntimeException('Valid host is required.');
        }

        return $host;
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
