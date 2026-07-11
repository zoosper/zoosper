<?php

declare(strict_types=1);

namespace Zoosper\Core\View;

use Zoosper\Core\Cache\CacheContext;
use Zoosper\Core\Cache\CacheKeyBuilder;
use Zoosper\Core\Site\CurrentSiteContext;
use Zoosper\Core\Url\CdnUrlResolver;

/**
 * Provides safe shared view context data to frontend/admin templates.
 *
 * The provider exposes public site context, CDN URL helpers and cache-key helper
 * objects to templates without requiring controllers to pass hard-coded store
 * codes. It must never include credentials, session IDs, CSRF tokens, OTPs,
 * TOTP secrets, recovery-code plaintext, reset tokens, SMTP passwords, payment
 * data or customer-private values.
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
    public function data(?string $themeCode = null, string $routeName = ''): array
    {
        $siteContext = $this->currentSiteContext->get();
        $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
        $path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';

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
}
