<?php

declare(strict_types=1);

namespace Zoosper\Core\Site;

use Zoosper\Site\Model\Site as DbSite;
use Zoosper\Site\Repository\SiteRepository;

/**
 * Resolves the current website/store/store-view context from host and path.
 *
 * Phase 1.34c: SiteRepository is now the primary source of truth. The legacy
 * config/sites.php array remains as a bootstrap fallback when no active DB site
 * matches the host, or when the site module/repository is not available yet.
 *
 * The resolver only handles public site metadata and must never handle
 * credentials, OTPs, TOTP secrets, recovery-code plaintext, reset tokens, SMTP
 * passwords, payment data or customer-private values.
 */
final readonly class SiteContextResolver
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        private array $config,
        private ?SiteRepository $sites = null,
    ) {
    }

    /**
     * Resolve site context from host/path, falling back to config/sites.php.
     */
    public function resolve(?string $host = null, string $path = '/'): SiteContext
    {
        $host = $this->normaliseHost($host ?? '');
        $path = $this->normalisePath($path);

        if ($this->sites !== null && $host !== '') {
            $site = $this->sites->findActiveByHost($host);
            if ($site !== null && $this->dbSiteMatchesPath($site, $path)) {
                return $this->contextFromDbSite($site);
            }
        }

        foreach ($this->activeStoreViews() as $storeView) {
            if ($this->matches($storeView, $host, $path)) {
                return $this->contextFromStoreView($storeView);
            }
        }

        return $this->contextFromStoreView($this->defaultStoreView());
    }

    /**
     * Resolve the default configured store view.
     */
    public function default(): SiteContext
    {
        return $this->contextFromStoreView($this->defaultStoreView());
    }

    /**
     * Build a rich Core SiteContext from the flattened DB site row.
     */
    private function contextFromDbSite(DbSite $site): SiteContext
    {
        return new SiteContext(
            websiteCode: $site->websiteCode,
            websiteName: $site->name,
            storeCode: $site->storeCode,
            storeName: $site->name,
            storeViewCode: $site->storeViewCode,
            storeViewName: $site->name,
            locale: $site->locale,
            currency: $site->currency,
            baseUrl: rtrim($site->baseUrl, '/'),
            pathPrefix: $this->normaliseOptionalPrefix($site->pathPrefix),
        );
    }

    private function dbSiteMatchesPath(DbSite $site, string $path): bool
    {
        $pathPrefix = $this->normaliseOptionalPrefix($site->pathPrefix);
        if ($pathPrefix === '') {
            return true;
        }

        return $path === $pathPrefix || str_starts_with($path, rtrim($pathPrefix, '/') . '/');
    }

    /**
     * Return all configured active store views.
     *
     * @return list<array<string, mixed>>
     */
    private function activeStoreViews(): array
    {
        $storeViews = $this->config['store_views'] ?? [];
        if (!is_array($storeViews)) {
            return [];
        }

        $normalised = [];
        foreach ($storeViews as $code => $storeView) {
            if (!is_array($storeView) || ($storeView['is_active'] ?? true) === false) {
                continue;
            }

            $storeView['store_view_code'] ??= is_string($code) ? $code : 'default';
            $normalised[] = $storeView;
        }

        usort(
            $normalised,
            static fn (array $a, array $b): int => strlen((string) ($b['path_prefix'] ?? '')) <=> strlen((string) ($a['path_prefix'] ?? '')),
        );

        return $normalised;
    }

    /**
     * Return the configured default store view row.
     *
     * @return array<string, mixed>
     */
    private function defaultStoreView(): array
    {
        $storeViews = $this->config['store_views'] ?? [];
        $defaultCode = (string) ($this->config['default_store_view'] ?? 'default');

        if (is_array($storeViews) && isset($storeViews[$defaultCode]) && is_array($storeViews[$defaultCode])) {
            $storeView = $storeViews[$defaultCode];
            $storeView['store_view_code'] ??= $defaultCode;

            return $storeView;
        }

        foreach ($this->activeStoreViews() as $storeView) {
            return $storeView;
        }

        return [
            'website_code' => 'main',
            'website_name' => 'Main Website',
            'store_code' => 'main',
            'store_name' => 'Main Store',
            'store_view_code' => 'default',
            'store_view_name' => 'Default Store View',
            'locale' => 'en_AU',
            'currency' => 'AUD',
            'base_url' => '',
            'domains' => [],
            'path_prefix' => '',
        ];
    }

    /**
     * Determine whether a config store view matches host and path.
     *
     * @param array<string, mixed> $storeView
     */
    private function matches(array $storeView, string $host, string $path): bool
    {
        $domains = $storeView['domains'] ?? [];
        $pathPrefix = $this->normaliseOptionalPrefix((string) ($storeView['path_prefix'] ?? ''));
        $domainMatches = false;

        if (is_array($domains) && $domains !== []) {
            foreach ($domains as $domain) {
                if ($this->normaliseHost((string) $domain) === $host) {
                    $domainMatches = true;
                    break;
                }
            }
        } else {
            $domainMatches = true;
        }

        if (!$domainMatches) {
            return false;
        }

        if ($pathPrefix === '') {
            return true;
        }

        return $path === $pathPrefix || str_starts_with($path, rtrim($pathPrefix, '/') . '/');
    }

    /**
     * Build a typed context from a config row.
     *
     * @param array<string, mixed> $storeView
     */
    private function contextFromStoreView(array $storeView): SiteContext
    {
        return new SiteContext(
            websiteCode: (string) ($storeView['website_code'] ?? 'main'),
            websiteName: (string) ($storeView['website_name'] ?? 'Main Website'),
            storeCode: (string) ($storeView['store_code'] ?? 'main'),
            storeName: (string) ($storeView['store_name'] ?? 'Main Store'),
            storeViewCode: (string) ($storeView['store_view_code'] ?? 'default'),
            storeViewName: (string) ($storeView['store_view_name'] ?? 'Default Store View'),
            locale: (string) ($storeView['locale'] ?? 'en_AU'),
            currency: (string) ($storeView['currency'] ?? 'AUD'),
            baseUrl: rtrim((string) ($storeView['base_url'] ?? ''), '/'),
            pathPrefix: $this->normaliseOptionalPrefix((string) ($storeView['path_prefix'] ?? '')),
        );
    }

    private function normaliseHost(string $host): string
    {
        $host = strtolower(trim($host));
        if (str_contains($host, ':')) {
            $host = explode(':', $host, 2)[0];
        }

        return $host;
    }

    private function normalisePath(string $path): string
    {
        $path = '/' . ltrim(trim($path), '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    private function normaliseOptionalPrefix(string $prefix): string
    {
        $prefix = trim($prefix);
        if ($prefix === '') {
            return '';
        }

        return $this->normalisePath($prefix);
    }
}
