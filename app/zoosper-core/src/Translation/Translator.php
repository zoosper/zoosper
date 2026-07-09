<?php

declare(strict_types=1);

namespace Zoosper\Core\Translation;

final readonly class Translator
{
    /** @param array<string, string> $messages */
    public function __construct(private array $messages)
    {
    }

    /** @param array<string, string|int|float> $replace */
    public function translate(string $key, array $replace = []): string
    {
        $message = $this->messages[$key] ?? $key;
        foreach ($replace as $name => $value) {
            $message = str_replace(':' . $name, (string) $value, $message);
        }
        return $message;
    }
}
