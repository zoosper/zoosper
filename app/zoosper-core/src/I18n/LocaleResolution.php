<?php

declare(strict_types=1);

namespace Zoosper\Core\I18n;

/**
 * Immutable locale resolution result for one runtime scope.
 *
 * The active locale is the locale selected for the current scope, while the
 * fallback locale is loaded first by the translation resolver. The scope value
 * is intentionally plain text for now so future admin/site/frontend resolvers
 * can share the same value object.
 */
final readonly class LocaleResolution
{
    public function __construct(
        public string $scope,
        public string $activeLocale,
        public string $fallbackLocale,
        public string $defaultLocale,
    ) {
    }
}
