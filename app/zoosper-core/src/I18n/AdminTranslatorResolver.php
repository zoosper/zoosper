<?php

declare(strict_types=1);

namespace Zoosper\Core\I18n;

/**
 * Resolves the admin translator through the configured locale resolver.
 *
 * This is the integration layer between locale selection and catalogue-backed
 * translation. It keeps controller/runtime code from separately knowing about
 * i18n config keys, locale fallback rules and translation file aggregation.
 */
final readonly class AdminTranslatorResolver
{
    /** @param array<string, mixed> $i18nConfig */
    public function __construct(
        private string $basePath,
        private array $i18nConfig = [],
    ) {
    }

    public function resolve(): TranslatorInterface
    {
        $locale = (new ConfiguredLocaleResolver($this->i18nConfig))->resolveAdminLocale();

        return (new TranslationResolver($this->basePath))->forResolution($locale);
    }

    public function resolveLocale(): LocaleResolution
    {
        return (new ConfiguredLocaleResolver($this->i18nConfig))->resolveAdminLocale();
    }
}
