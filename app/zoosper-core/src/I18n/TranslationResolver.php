<?php

declare(strict_types=1);

namespace Zoosper\Core\I18n;

/**
 * Resolves catalogue-backed translators for a locale.
 *
 * This service wires the translation file aggregator into the translator
 * contract so controllers and runtime services can use module-owned
 * dictionaries instead of falling back to source strings only.
 */
final readonly class TranslationResolver
{
    public function __construct(private string $basePath)
    {
    }

    public function forLocale(string $locale = 'en_AU', string $fallbackLocale = 'en_AU'): TranslatorInterface
    {
        $locale = $this->normaliseLocale($locale, 'en_AU');
        $fallbackLocale = $this->normaliseLocale($fallbackLocale, 'en_AU');
        $catalogue = (new TranslationFileAggregator($this->basePath))->catalogue($locale, $fallbackLocale);

        return new ArrayTranslator($catalogue);
    }

    public function forResolution(LocaleResolution $locale): TranslatorInterface
    {
        return $this->forLocale($locale->activeLocale, $locale->fallbackLocale);
    }

    private function normaliseLocale(string $locale, string $fallback): string
    {
        $locale = trim($locale);

        return $locale === '' ? $fallback : $locale;
    }
}
