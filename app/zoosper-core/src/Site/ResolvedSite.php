<?php

declare(strict_types=1);

namespace Zoosper\Core\Site;

/**
 * Core-owned immutable snapshot of the site fields required for request context.
 *
 * Feature modules may hydrate this value object, but core consumers should not
 * need to import feature-module repository or model classes.
 */
final readonly class ResolvedSite
{
    public function __construct(
        public int|string|null $id,
        public string $code,
        public string $name,
        public string $baseUrl = '',
        public bool $isActive = true,
        public ?string $websiteCode = null,
        public ?string $websiteName = null,
        public ?string $storeCode = null,
        public ?string $storeName = null,
        public ?string $storeViewCode = null,
        public ?string $storeViewName = null,
        public ?string $locale = null,
        public ?string $currency = null,
        public ?string $pathPrefix = null,
    ) {
    }
}










