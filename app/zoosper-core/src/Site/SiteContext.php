<?php

declare(strict_types=1);

namespace Zoosper\Core\Site;

/**
 * Immutable website/store/store-view context for the current request.
 *
 * Feature code should depend on this context instead of hard-coding store codes,
 * domains, locales or currencies. The context is safe metadata only and must not
 * contain OTPs, TOTP secrets, recovery-code plaintext, reset tokens, SMTP
 * passwords, payment data, signed private URLs or customer-private values.
 */
final readonly class SiteContext
{
    public function __construct(
        public string $websiteCode,
        public string $websiteName,
        public string $storeCode,
        public string $storeName,
        public string $storeViewCode,
        public string $storeViewName,
        public string $locale,
        public string $currency,
        public string $baseUrl,
        public string $pathPrefix = '',
        public ?int $siteId = null,
    ) {
    }

    /**
     * Return a concise debug-safe array for diagnostics.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'website_code' => $this->websiteCode,
            'website_name' => $this->websiteName,
            'store_code' => $this->storeCode,
            'store_name' => $this->storeName,
            'store_view_code' => $this->storeViewCode,
            'store_view_name' => $this->storeViewName,
            'locale' => $this->locale,
            'currency' => $this->currency,
            'base_url' => $this->baseUrl,
            'path_prefix' => $this->pathPrefix,
            'site_id' => $this->siteId !== null ? (string) $this->siteId : '',
        ];
    }
}
