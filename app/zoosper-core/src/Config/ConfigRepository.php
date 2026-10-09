<?php

declare(strict_types=1);

namespace Zoosper\Core\Config;

/**
 * Immutable application configuration.
 *
 * Phase 1.32: config can be assembled from layered sources (module defaults
 * merged under root overrides) via fromArray(). The root-only fromPath() loader
 * is preserved unchanged for CLI callers.
 */
final readonly class ConfigRepository
{
    /** @param array<string, mixed> $items */
    private function __construct(private array $items)
    {
    }

    public static function fromPath(string $path): self
    {
        $items = [];

        $files = glob(rtrim($path, '/') . '/*.php');
        foreach ($files === false ? [] : $files as $file) {
            $items[basename($file, '.php')] = self::loadFile($file);
        }

        return new self($items);
    }

    private static function loadFile(string $file): mixed
    {
        return require $file;
    }

    /** @param array<string, mixed> $items */
    public static function fromArray(array $items): self
    {
        return new self($items);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->items;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    /** @return array<array-key, mixed> */
    public function array(string $key): array
    {
        $value = $this->get($key, []);

        return is_array($value) ? $value : [];
    }
}










