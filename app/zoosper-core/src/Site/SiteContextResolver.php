<?php

declare(strict_types=1);

namespace Zoosper\Core\Site;

use Zoosper\Core\Site\SiteLookupInterface;
use Zoosper\Core\Site\NullSiteLookup;
use Zoosper\Core\Site\ResolvedSite;

/**
 * Resolves the current website/store/store-view context from host and path.
 *
 * Phase 1.34c: SiteLookupInterface is now the primary source of truth for core. The legacy
 * config/sites.php array remains as a bootstrap fallback when no active DB site
 * matches the host, or when the site module/repository is not available yet.
 *
 * Phase 1.34e: DB-backed contexts expose the numeric siteId so page/API hot
 * paths can use Request::siteContext() without re-resolving through the legacy
 * legacy feature-module site resolver path.
 */
final readonly class SiteContextResolver
{
    
    private SiteLookupInterface $sites;
/** @param array<string, mixed> $config */
    public function __construct(
        private array $config,
        ?object $sites = null,
    ) {
        $this->sites = $this->normaliseSiteLookup($sites);
    }

    public function resolve(?string $host = null, string $path = '/'): SiteContext
    {
        $host = $this->normaliseHost($host ?? '');
        $path = $this->normalisePath($path);

        if ($this->sites !== null && $host !== '') {
            $site = $this->sites->findActiveByHost($host);
            if ($site !== null && $this->resolvedSiteMatchesPath($site, $path)) {
                return $this->contextFromResolvedSite($site);
            }
        }

        foreach ($this->activeStoreViews() as $storeView) {
            if ($this->matches($storeView, $host, $path)) {
                return $this->contextFromStoreView($storeView);
            }
        }

        return $this->contextFromStoreView($this->defaultStoreView());
    }

    public function default(): SiteContext
    {
        return $this->contextFromStoreView($this->defaultStoreView());
    }

    private function contextFromResolvedSite(ResolvedSite $site): SiteContext
    {
        $websiteCode = $this->nonEmptyString($site->websiteCode ?? null, $this->nonEmptyString($site->code, 'default'));
        $websiteName = $this->nonEmptyString($site->websiteName ?? null, $this->nonEmptyString($site->name, 'Default Website'));
        $storeCode = $this->nonEmptyString($site->storeCode ?? null, $this->nonEmptyString($site->code, $websiteCode));
        $storeName = $this->nonEmptyString($site->storeName ?? null, $this->nonEmptyString($site->name, 'Default Store'));
        $storeViewCode = $this->nonEmptyString($site->storeViewCode ?? null, $storeCode);
        $storeViewName = $this->nonEmptyString($site->storeViewName ?? null, $this->nonEmptyString($site->name, 'Default Store View'));
        $locale = $this->nonEmptyString($site->locale ?? null, $this->configString(['locale', 'default_locale'], 'en_US'));
        $currency = $this->nonEmptyString($site->currency ?? null, $this->configString(['currency', 'default_currency'], 'USD'));
        $baseUrl = $this->normaliseBaseUrl($site->baseUrl);
        $pathPrefix = $this->normaliseOptionalPrefix($site->pathPrefix ?? null);
        $siteId = is_numeric($site->id) ? (int) $site->id : null;

        return new SiteContext(
            websiteCode: $websiteCode,
            websiteName: $websiteName,
            storeCode: $storeCode,
            storeName: $storeName,
            storeViewCode: $storeViewCode,
            storeViewName: $storeViewName,
            locale: $locale,
            currency: $currency,
            baseUrl: $baseUrl,
            pathPrefix: $pathPrefix,
            siteId: $siteId,
        );
    }

    private function resolvedSiteMatchesPath(ResolvedSite $site, string $path): bool
    {
        $pathPrefix = $this->normaliseOptionalPrefix($site->pathPrefix);
        if ($pathPrefix === '') {
            return true;
        }

        return $path === $pathPrefix || str_starts_with($path, rtrim($pathPrefix, '/') . '/');
    }

    /** @return list<array<string, mixed>> */
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

    /** @return array<string, mixed> */
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

    /** @param array<string, mixed> $storeView */
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

    /** @param array<string, mixed> $storeView */
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

    private function normaliseSiteLookup(?object $sites): SiteLookupInterface
    {
        if ($sites instanceof SiteLookupInterface) {
            return $sites;
        }

        if ($sites === null) {
            return new NullSiteLookup();
        }

        return new class ($sites) implements SiteLookupInterface {
            public function __construct(
                private readonly object $repository,
            ) {
            }

            public function findByHost(string $host): ?ResolvedSite
            {
                return $this->callRepository(['findByHost', 'findByDomain', 'findActiveByHost'], $host);
            }

            public function findActiveByHost(string $host): ?ResolvedSite
            {
                return $this->callRepository(['findActiveByHost', 'findByHost', 'findByDomain'], $host);
            }

            public function findByCode(string $code): ?ResolvedSite
            {
                return $this->callRepository(['findByCode', 'findOneByCode'], $code);
            }

            public function findDefault(): ?ResolvedSite
            {
                foreach (['findDefault', 'getDefault', 'default'] as $method) {
                    if (method_exists($this->repository, $method)) {
                        return $this->toResolvedSite($this->repository->{$method}());
                    }
                }

                return null;
            }

            /**
             * @param list<string> $methods
             */
            private function callRepository(array $methods, string $value): ?ResolvedSite
            {
                foreach ($methods as $method) {
                    if (method_exists($this->repository, $method)) {
                        return $this->toResolvedSite($this->repository->{$method}($value));
                    }
                }

                return null;
            }

            private function toResolvedSite(mixed $site): ?ResolvedSite
            {
                if ($site === null) {
                    return null;
                }

                if ($site instanceof ResolvedSite) {
                    return $site;
                }

                if (is_array($site)) {
                    return new ResolvedSite(
                        $site['id'] ?? null,
                        (string) ($site['code'] ?? ''),
                        (string) ($site['name'] ?? ''),
                        (string) ($site['base_url'] ?? $site['baseUrl'] ?? ''),
                        (bool) ($site['is_active'] ?? $site['isActive'] ?? true),
                    );
                }

                if (is_object($site)) {
                    return new ResolvedSite(
                        id: $this->read($site, ['id', 'siteId']),
                        code: (string) ($this->read($site, ['code', 'storeCode', 'store_code']) ?? ''),
                        name: (string) ($this->read($site, ['name', 'storeName', 'store_name']) ?? ''),
                        baseUrl: (string) ($this->read($site, ['baseUrl', 'base_url']) ?? ''),
                        isActive: (bool) ($this->read($site, ['isActive', 'is_active', 'active']) ?? true),
                        websiteCode: $this->nullableString($this->read($site, ['websiteCode', 'website_code'])),
                        websiteName: $this->nullableString($this->read($site, ['websiteName', 'website_name'])),
                        storeCode: $this->nullableString($this->read($site, ['storeCode', 'store_code', 'code'])),
                        storeName: $this->nullableString($this->read($site, ['storeName', 'store_name', 'name'])),
                        storeViewCode: $this->nullableString($this->read($site, ['storeViewCode', 'store_view_code'])),
                        storeViewName: $this->nullableString($this->read($site, ['storeViewName', 'store_view_name'])),
                        locale: $this->nullableString($this->read($site, ['locale'])),
                        currency: $this->nullableString($this->read($site, ['currency'])),
                        pathPrefix: $this->nullableString($this->read($site, ['pathPrefix', 'path_prefix'])),
                    );
                }

                return null;
            }

            private function nullableString(mixed $value): ?string
            {
                if ($value === null) {
                    return null;
                }

                $value = trim((string) $value);

                return $value !== '' ? $value : null;
            }
            /**
             * @param list<string> $names
             */
            private function read(object $site, array $names): mixed
            {
                foreach ($names as $name) {
                    if (isset($site->{$name})) {
                        return $site->{$name};
                    }

                    $getter = 'get' . ucfirst($name);
                    if (method_exists($site, $getter)) {
                        return $site->{$getter}();
                    }
                }

                return null;
            }
        };
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

    private function normaliseBaseUrl(mixed $baseUrl): string
    {
        return trim((string) ($baseUrl ?? ''));
    }
    /**
     * @param list<string> $keys
     */
    private function configString(array $keys, string $fallback): string
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $this->config)) {
                $value = trim((string) ($this->config[$key] ?? ''));
                if ($value !== '') {
                    return $value;
                }
            }
        }

        return $fallback;
    }
    private function nonEmptyString(mixed $value, string $fallback): string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : $fallback;
    }
    private function normaliseOptionalPrefix(?string $prefix): string
    {
        $prefix = trim((string) ($prefix ?? ''));
        if ($prefix === '') {
            return '';
        }

        return $this->normalisePath($prefix);
    }
}
