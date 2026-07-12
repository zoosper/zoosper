<?php

declare(strict_types=1);

namespace Zoosper\Core\I18n;

/**
 * Resolves catalogue-backed translators for a locale.
 *
 * This service is intentionally small while locale selection is still being
 * formalised. It wires the existing translation file aggregator into the
 * translator contract so controllers/services can use module-owned dictionaries
 * instead of always falling back to `IdentityTranslator`.
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

    private function normaliseLocale(string $locale, string $fallback): string
    {
        $locale = trim($locale);

        return $locale === '' ? $fallback : $locale;
    }
}
