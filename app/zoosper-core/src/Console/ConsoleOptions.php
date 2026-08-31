<?php

declare(strict_types=1);

namespace Zoosper\Core\Console;

use RuntimeException;

/**
 * Shared CLI option-parsing helpers for module-owned console commands.
 *
 * Console/kernel decoupling phase: extracted from bin/zoosper's inline
 * parseOptions()/required()/slugify() functions so that module-owned
 * commands (admin:create, site:create, page:create, and any future
 * third-party command) can reuse identical --key=value parsing behaviour
 * without each module duplicating the same code.
 *
 * required() throws RuntimeException rather than calling exit() directly —
 * this keeps the helper testable (no hard process exits inside a shared,
 * reusable class) and lets each command's run() method decide how to report
 * the error via ConsoleOutput, while still resulting in the same
 * stderr-message + non-zero-exit-code behaviour end users saw before.
 */
final class ConsoleOptions
{
    /**
     * Parse a list of raw CLI arguments into a --key=value option map.
     * Arguments that are not in --key=value form are ignored.
     *
     * @param list<string> $args
     * @return array<string, string>
     */
    public static function parse(array $args): array
    {
        $options = [];
        foreach ($args as $arg) {
            if (!str_starts_with($arg, '--') || !str_contains($arg, '=')) {
                continue;
            }
            [$key, $value] = explode('=', substr($arg, 2), 2);
            $options[$key] = $value;
        }

        return $options;
    }

    /**
     * Return a required option value, or throw if missing/empty.
     *
     * @param array<string, string> $options
     */
    public static function required(array $options, string $key): string
    {
        if (!array_key_exists($key, $options) || $options[$key] === '') {
            throw new RuntimeException("Missing required option: --{$key}=...");
        }

        return $options[$key];
    }

    public static function slugify(string $value): string
    {
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($value)) ?: 'page';

        return trim($slug, '-');
    }
}










