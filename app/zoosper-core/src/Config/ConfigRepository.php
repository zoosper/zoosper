<?php

declare(strict_types=1);

namespace Zoosper\Core\Config;

final readonly class ConfigRepository
{
    /**
     * @param array<string, mixed> $items
     */
    private function __construct(private array $items)
    {
    }

    public static function fromPath(string $path): self
    {
        $items = [];

        foreach (glob($path . '/*.php') ?: [] as $file) {
            $items[basename($file, '.php')] = require $file;
        }

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

    /**
     * @return array<string, string>
     */
    public function array(string $key): array
    {
        $value = $this->get($key, []);

        return is_array($value) ? $value : [];
    }
}
