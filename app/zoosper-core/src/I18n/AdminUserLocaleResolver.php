<?php

declare(strict_types=1);

namespace Zoosper\Core\I18n;

/**
 * Resolves the active admin locale from an optional admin-user preference.
 *
 * The resolver is deliberately tolerant about the user object because the auth
 * model may continue to evolve. It reads a public `locale` property or a
 * `locale()` getter if one exists. Invalid locale values are ignored so they
 * cannot influence translation-file lookup paths.
 */
final readonly class AdminUserLocaleResolver
{
    public function __construct(private LocaleResolverInterface $configuredResolver)
    {
    }

    public function resolveForAdminUser(?object $adminUser): LocaleResolution
    {
        $configured = $this->configuredResolver->resolveAdminLocale();
        $candidate = $this->extractLocale($adminUser);

        if (!$this->isValidLocale($candidate)) {
            return $configured;
        }

        return new LocaleResolution(
            scope: 'admin',
            activeLocale: (string) $candidate,
            fallbackLocale: $configured->fallbackLocale,
            defaultLocale: $configured->defaultLocale,
        );
    }

    public function isValidLocale(?string $locale): bool
    {
        return is_string($locale) && preg_match('/^[a-z]{2}_[A-Z]{2}$/', $locale) === 1;
    }

    private function extractLocale(?object $adminUser): ?string
    {
        if ($adminUser === null) {
            return null;
        }

        if (property_exists($adminUser, 'locale')) {
            $value = $adminUser->locale;

            return is_string($value) && trim($value) !== '' ? trim($value) : null;
        }

        if (method_exists($adminUser, 'locale')) {
            $value = $adminUser->locale();

            return is_string($value) && trim($value) !== '' ? trim($value) : null;
        }

        return null;
    }
}
