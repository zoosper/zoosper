<?php

declare(strict_types=1);

namespace Zoosper\Core\Config;

final readonly class ConfigRepository
{
    /** @param array<string, mixed> $items */
    private function __construct(private array $items)
    {
    }

    public static function fromPath(string $configPath): self
    {
        $items = [];
        foreach (glob($configPath . '/*.php') ?: [] as $file) {
            $key = basename($file, '.php');
            /** @var array<string, mixed> $value */
            $value = require $file;
            $items[$key] = $value;
        }

        return new self($items);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = $this->items;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /** @return array<string, string> */
    public function array(string $key): array
    {
        $value = $this->get($key, []);
        return is_array($value) ? $value : [];
    }
}
