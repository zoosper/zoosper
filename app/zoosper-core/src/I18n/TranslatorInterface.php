<?php

declare(strict_types=1);

namespace Zoosper\Core\I18n;

/**
 * Translates system-facing strings before they are rendered or flashed.
 *
 * This contract keeps controllers and services free from hard-coded final copy
 * while allowing future locale-aware translators to be added through DI,
 * preferences or module configuration.
 */
interface TranslatorInterface
{
    /**
     * @param array<string, scalar|null> $parameters Placeholder values keyed by name.
     */
    public function translate(string $message, array $parameters = []): string;
}
