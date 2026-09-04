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
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\AdminGrid\{AdminCollectionGrid,AdminCollectionGridQuery};
use Zoosper\Site\Admin\Grid\SiteDomainGrid;
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
        private ?AdminUrlGenerator $adminUrls = null,
        private ?SiteDomainGrid $grid = null,
        private ?AdminCollectionGrid $collectionGrid = null,
    ) {
    }

    public function index(Request $request): Response
    {
        $user=$this->currentAdminUser();
        if($this->grid===null||$this->collectionGrid===null)throw new RuntimeException('Admin Grid services are required for Site Domains.');
        $definition=$this->grid->definition();
        $gridHtml=$this->collectionGrid->render($user->id,'admin.site-domains',$this->adminUrl('site-domains'),$definition,$this->grid,AdminCollectionGridQuery::values($request,$definition),AdminCollectionGridQuery::bookmark($request))['html'];
        $html='<section class="site-domains-index" aria-labelledby="site-domains-index-title"><header class="site-domains-index__header"><p class="site-domains-index__breadcrumb">System / Site Domains</p><div class="site-domains-index__heading-row"><div><h1 id="site-domains-index-title" class="site-domains-index__title">Site Domains</h1><p class="site-domains-index__description">Manage hostnames and their assigned sites.</p></div><a class="button site-domains-index__create" href="'.$this->adminUrl('site-domains/create').'">Create domain</a></div></header>'.$gridHtml.'</section>';
        return $this->html('Site Domains',$html,$user,shellTitle:'');
    }

    public function create(Request $request): Response
    {
        $user = $this->currentAdminUser();

        return $this->html('Add site domain', $this->form('' . $this->adminUrl('site-domains/create') . ''), $user);
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

            return Response::redirect($this->adminUrl('site-domains'));
        } catch (RuntimeException $exception) {
            return $this->html('Add site domain', $this->form('' . $this->adminUrl('site-domains/create') . '', null, $exception->getMessage(), $form), $user, 422);
        }
    }

    public function edit(Request $request): Response
    {
        $user = $this->currentAdminUser();
        $domain = $this->domainFromRequest($request);
        if ($domain === null) {
            return $this->html('Domain not found', '<section class="card"><p class="error">Site domain not found.</p></section>', $user, 404);
        }

        return $this->html('Edit site domain', $this->form($this->adminUrl('site-domains/edit', ['id' => $domain->id]), $domain), $user);
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

            return Response::redirect($this->adminUrl('site-domains'));
        } catch (RuntimeException $exception) {
            return $this->html('Edit site domain', $this->form($this->adminUrl('site-domains/edit', ['id' => $domain->id]), $domain, $exception->getMessage(), $form), $user, 422);
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
            . '<p><button type="submit">Save domain</button> <a href="' . $this->e($this->adminUrl('site-domains')) . '">Cancel</a></p>'
            . '</form></section>';
    }

    private function domainFromRequest(Request $request): ?SiteDomain
    {
        $id = $request->query('id');

        return $id !== null && ctype_digit($id) ? $this->domains->findById((int) $id) : null;
    }

    private function html(string $title, string $content, AdminUser $user, int $statusCode = 200, ?string $shellTitle = null): Response
    {
        return Response::html($this->layout->render($title, $content, $user, 'site-domains', $shellTitle), $statusCode);
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

    /** @param array<string, scalar|null> $query */
    private function adminUrl(string $path, array $query = []): string
    {
        if ($this->adminUrls !== null) {
            return $this->adminUrls->url($path, $query);
        }

        $url = '/admin/' . ltrim($path, '/');
        $queryString = http_build_query($query, '', '&', PHP_QUERY_RFC3986);

        return $queryString === '' ? $url : $url . '?' . $queryString;
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}










