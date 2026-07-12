<?php

declare(strict_types=1);

namespace Zoosper\Core\I18n;

/**
 * Safe fallback translator used until locale-aware translations are configured.
 *
 * It returns the original message and replaces simple placeholders such as
 * `{name}`. This keeps the call sites translation-ready without changing
 * current runtime behaviour.
 */
final readonly class IdentityTranslator implements TranslatorInterface
{
    public function translate(string $message, array $parameters = []): string
    {
        if ($parameters === []) {
            return $message;
        }

        $replacements = [];
        foreach ($parameters as $key => $value) {
            $replacements['{' . $key . '}'] = (string) $value;
        }

        return strtr($message, $replacements);
    }
}
