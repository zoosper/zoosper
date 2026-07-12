<?php

declare(strict_types=1);

namespace Zoosper\Core\I18n;

/**
 * Resolves active and fallback locales for runtime scopes.
 */
interface LocaleResolverInterface
{
    /** @param array<string, mixed> $context */
    public function resolveAdminLocale(array $context = []): LocaleResolution;

    /** @param array<string, mixed> $context */
    public function resolveSiteLocale(array $context = []): LocaleResolution;
}
