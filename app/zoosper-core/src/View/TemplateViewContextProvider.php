<?php

declare(strict_types=1);

namespace Zoosper\Core\View;

use Zoosper\Core\Cache\CacheContext;
use Zoosper\Core\Cache\CacheKeyBuilder;
use Zoosper\Core\Site\CurrentSiteContext;
use Zoosper\Core\Site\SiteContext;
use Zoosper\Core\Url\CdnUrlResolver;

/**
 * Provides safe shared view context data to frontend/admin templates.
 *
 * Phase 1.34d foundation: callers can now pass the request-carried SiteContext,
 * host and path explicitly. This removes the provider's direct $_SERVER reads and
 * lets the render stack use immutable request state rather than global state.
 *
 * The legacy CurrentSiteContext fallback remains temporarily for call sites that
 * have not yet been threaded with Request. It is immutable since 1.34a and will
 * be retired once every renderer call passes request context explicitly.
 */
final readonly class TemplateViewContextProvider
{
    public function __construct(
        private CurrentSiteContext $currentSiteContext,
        private CdnUrlResolver $cdnUrlResolver,
        private CacheKeyBuilder $cacheKeyBuilder,
    ) {
    }

    /**
     * Build shared template data for the current request.
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
        $siteContext ??= $this->currentSiteContext->get();
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
            'currentSiteContext' => $this->currentSiteContext,
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
