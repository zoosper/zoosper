<?php

declare(strict_types=1);

namespace Zoosper\Core\Cache;

use Zoosper\Core\Site\SiteContext;

/**
 * Immutable cache context used to partition public page, block and fragment cache keys.
 *
 * The context captures only safe routing/site metadata that affects rendered
 * output. It must never contain credentials, session IDs, CSRF tokens, OTPs,
 * TOTP secrets, recovery-code plaintext, reset tokens, SMTP passwords, payment
 * data or customer-private values.
 */
final readonly class CacheContext
{
    public function __construct(
        public string $websiteCode,
        public string $storeCode,
        public string $storeViewCode,
        public string $locale,
        public string $currency,
        public string $themeCode,
        public string $host,
        public string $path,
        public bool $isAuthenticated = false,
        public string $customerGroup = 'guest',
        public string $routeName = '',
    ) {
    }

    /**
     * Build cache context from the resolved site context and current request metadata.
     */
    public static function fromSiteContext(
        SiteContext $siteContext,
        string $host,
        string $path,
        string $themeCode = 'default',
        bool $isAuthenticated = false,
        string $customerGroup = 'guest',
        string $routeName = '',
    ): self {
        return new self(
            websiteCode: $siteContext->websiteCode,
            storeCode: $siteContext->storeCode,
            storeViewCode: $siteContext->storeViewCode,
            locale: $siteContext->locale,
            currency: $siteContext->currency,
            themeCode: $themeCode,
            host: self::normaliseHost($host),
            path: self::normalisePath($path),
            isAuthenticated: $isAuthenticated,
            customerGroup: $customerGroup,
            routeName: $routeName,
        );
    }

    /**
     * Return the dimensions that must vary public full-page cache.
     *
     * @return array<string, string>
     */
    public function publicPageDimensions(): array
    {
        return [
            'website' => $this->websiteCode,
            'store' => $this->storeCode,
            'store_view' => $this->storeViewCode,
            'locale' => $this->locale,
            'currency' => $this->currency,
            'theme' => $this->themeCode,
            'host' => $this->host,
            'path' => $this->path,
            'route' => $this->routeName,
        ];
    }

    /**
     * Return dimensions that may vary private/user-specific fragments.
     *
     * @return array<string, string>
     */
    public function privateFragmentDimensions(): array
    {
        return $this->publicPageDimensions() + [
            'auth' => $this->isAuthenticated ? 'yes' : 'no',
            'customer_group' => $this->customerGroup,
        ];
    }

    /**
     * Return a debug-safe array for CLI diagnostics.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->privateFragmentDimensions();
    }

    private static function normaliseHost(string $host): string
    {
        $host = strtolower(trim($host));
        if (str_contains($host, ':')) {
            $host = explode(':', $host, 2)[0];
        }

        return $host;
    }

    private static function normalisePath(string $path): string
    {
        $path = '/' . ltrim(trim($path), '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
