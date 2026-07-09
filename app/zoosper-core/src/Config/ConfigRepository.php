<?php
declare(strict_types=1);

namespace Zoosper\Core\Config;
final readonly class ConfigRepository
{
    private function __construct(private array $items)
    {
    }

    public static function fromPath(string $path): self
    {
        $items = [];
        foreach (glob($path . '/*.php') ?: [] as $f) $items[basename($f, '.php')] = require $f;
        return new self($items);
    }

    public function array(string $key): array
    {
        $v = $this->get($key, []);
        return is_array($v) ? $v : [];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $v = $this->items;
        foreach (explode('.', $key) as $s) {
            if (!is_array($v) || !array_key_exists($s, $v)) return $default;
            $v = $v[$s];
        }
        return $v;
    }
}
