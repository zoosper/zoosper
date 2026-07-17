<?php

declare(strict_types=1);

namespace Zoosper\Site\Model;

/**
 * A site row - flattened to represent one store view (Phase 1.34, Option A).
 *
 * The rich store-view dimensions (locale, currency, base_url, website/store/
 * store-view codes, path_prefix) are stored directly on the sites table so the
 * DB is the single source of truth. All new fields default to safe values, so
 * existing callers and rows without the enriched columns remain valid.
 */
final readonly class Site
{
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public string $status,
        public ?string $homepageSlug = null,
        public string $themeCode = 'default',
        public string $locale = 'en_AU',
        public string $currency = 'AUD',
        public string $baseUrl = '',
        public string $websiteCode = 'main',
        public string $storeCode = 'main',
        public string $storeViewCode = 'default',
        public string $pathPrefix = '',
    ) {
    }
}
