<?php

declare(strict_types=1);

namespace Zoosper\Core\I18n;

/**
 * Locale resolver backed by configuration.
 *
 * This is the safe baseline resolver until Zoosper has admin-user preferences,
 * per-site locale settings and request-aware locale negotiation. It centralises
 * locale fallback logic so controllers do not need to know config key details.
 */
final readonly class ConfiguredLocaleResolver implements LocaleResolverInterface
{
    /** @param array<string, mixed> $config */
    public function __construct(private array $config = [])
    {
    }

    public function resolveAdminLocale(array $context = []): LocaleResolution
    {
        $defaultLocale = $this->normaliseLocale($this->config['default_locale'] ?? null, 'en_AU');
        $activeLocale = $this->normaliseLocale($this->config['admin_locale'] ?? null, $defaultLocale);
        $fallbackLocale = $this->normaliseLocale($this->config['fallback_locale'] ?? null, $defaultLocale);

        return new LocaleResolution('admin', $activeLocale, $fallbackLocale, $defaultLocale);
    }

    public function resolveSiteLocale(array $context = []): LocaleResolution
    {
        $defaultLocale = $this->normaliseLocale($this->config['default_locale'] ?? null, 'en_AU');
        $activeLocale = $this->normaliseLocale($this->config['site_locale'] ?? null, $defaultLocale);
        $fallbackLocale = $this->normaliseLocale($this->config['fallback_locale'] ?? null, $defaultLocale);

        return new LocaleResolution('site', $activeLocale, $fallbackLocale, $defaultLocale);
    }

    private function normaliseLocale(mixed $value, string $fallback): string
    {
        $locale = trim((string) ($value ?? ''));

        return $locale === '' ? $fallback : $locale;
    }
}
