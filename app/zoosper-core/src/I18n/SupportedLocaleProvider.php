<?php

declare(strict_types=1);

namespace Zoosper\Core\I18n;

/**
 * Provides the list of locale options that may be shown in admin UI.
 *
 * Locale options are intentionally config-driven so future modules/projects can
 * add languages without editing controllers or templates. Locale codes are
 * strictly validated before being returned because locale codes are later used
 * to locate translation files.
 */
final readonly class SupportedLocaleProvider
{
    /** @param array<string, mixed> $i18nConfig */
    public function __construct(private array $i18nConfig = [])
    {
    }

    /**
     * @return array<string, string> Locale code to human-readable label map.
     */
    public function adminLocales(): array
    {
        $configured = $this->i18nConfig['supported_admin_locales'] ?? null;
        if (!is_array($configured) || $configured === []) {
            $configured = [
                'en_AU' => 'English (Australia)',
            ];
        }

        $locales = [];
        foreach ($configured as $code => $label) {
            if (is_int($code)) {
                $code = is_string($label) ? $label : '';
                $label = $code;
            }

            if (!$this->isValidLocaleCode((string) $code)) {
                continue;
            }

            $locales[(string) $code] = is_string($label) && trim($label) !== ''
                ? trim($label)
                : (string) $code;
        }

        return $locales !== [] ? $locales : ['en_AU' => 'English (Australia)'];
    }

    public function isSupportedAdminLocale(?string $locale): bool
    {
        return is_string($locale) && array_key_exists($locale, $this->adminLocales());
    }

    private function isValidLocaleCode(string $locale): bool
    {
        return preg_match('/^[a-z]{2}_[A-Z]{2}$/', $locale) === 1;
    }
}
