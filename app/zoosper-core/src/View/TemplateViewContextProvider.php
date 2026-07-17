<?php

declare(strict_types=1);

namespace Zoosper\Core\View;

use InvalidArgumentException;
use Zoosper\Core\Cache\CacheContext;
use Zoosper\Core\Cache\CacheKeyBuilder;
use Zoosper\Core\Site\SiteContext;
use Zoosper\Core\Url\CdnUrlResolver;

/**
 * Provides safe shared view context data to frontend/admin templates.
 *
 * Phase 1.34g retires the legacy site-context fallback from the render
 * path. Callers must now pass the request-carried SiteContext, or an explicit
 * SiteContext derived from the known Site model for non-request admin previews.
 * This keeps cache/render dimensions request-scoped and prevents accidental
 * fallback to a container-held default site.
 */
final readonly class TemplateViewContextProvider
{
    public function __construct(
        private CdnUrlResolver $cdnUrlResolver,
        private CacheKeyBuilder $cacheKeyBuilder,
    ) {
    }

    /**
     * Build shared template data for the current render.
     *
     * Explicit controller/template data should always override this shared data
     * when TemplateRenderer merges arrays.
     *
     * @return array<string, mixed>
     */
    public function data(
        ?string $themeCode = null,
        string $routeName = '',
        ?SiteContext $siteContext = null,
        string $host = '',
        string $path = '/',
    ): array {
        if ($siteContext === null) {
            throw new InvalidArgumentException('Template view context requires an explicit SiteContext. Pass Request::siteContext() or a Site-derived context.');
        }

        $host = $this->normaliseHost($host !== '' ? $host : $this->hostFromSiteContext($siteContext));
        $path = $this->normalisePath($path);

        $cacheContext = CacheContext::fromSiteContext(
            siteContext: $siteContext,
            host: $host,
            path: $path,
            themeCode: $themeCode ?? 'default',
            isAuthenticated: false,
            customerGroup: 'guest',
            routeName: $routeName,
        );

        return [
            'siteContext' => $siteContext,
            'cdn' => $this->cdnUrlResolver,
            'cacheContext' => $cacheContext,
            'cacheKeys' => $this->cacheKeyBuilder,
        ];
    }

    private function hostFromSiteContext(SiteContext $siteContext): string
    {
        $host = parse_url($siteContext->baseUrl, PHP_URL_HOST);

        return is_string($host) ? $host : '';
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
}
