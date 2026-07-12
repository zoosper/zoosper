<?php

declare(strict_types=1);

namespace Zoosper\Core\I18n;

/**
 * Immutable in-memory translation catalogue for one resolved locale.
 *
 * The catalogue intentionally stores message IDs as plain source strings for
 * now. This keeps call sites simple while allowing a later phase to introduce
 * IDs, domains, caching, pluralisation or ICU formatting without changing the
 * translator contract.
 */
final readonly class TranslationCatalogue
{
    /**
     * @param array<string, string> $messages
     */
    public function __construct(
        public string $locale,
        private array $messages = [],
    ) {
    }

    public function has(string $message): bool
    {
        return array_key_exists($message, $this->messages);
    }

    public function get(string $message): string
    {
        return $this->messages[$message] ?? $message;
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return $this->messages;
    }
}
