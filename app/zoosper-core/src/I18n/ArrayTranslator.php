<?php

declare(strict_types=1);

namespace Zoosper\Core\I18n;

/**
 * Locale-aware translator backed by a translation catalogue.
 *
 * For Phase 0.90 this class is intentionally small: it resolves exact message
 * strings from the catalogue and performs safe placeholder replacement. It can
 * be wired into the container in a later phase once admin/site locale resolution
 * has been formalised.
 */
final readonly class ArrayTranslator implements TranslatorInterface
{
    public function __construct(private TranslationCatalogue $catalogue)
    {
    }

    public function translate(string $message, array $parameters = []): string
    {
        $translated = $this->catalogue->get($message);
        if ($parameters === []) {
            return $translated;
        }

        $replacements = [];
        foreach ($parameters as $key => $value) {
            $replacements['{' . $key . '}'] = (string) $value;
        }

        return strtr($translated, $replacements);
    }
}
