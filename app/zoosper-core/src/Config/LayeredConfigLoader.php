<?php

declare(strict_types=1);

namespace Zoosper\Core\Config;

/**
 * Merges ordered config layers using explicit precedence.
 */
final class LayeredConfigLoader
{
    /**
     * @param list<array{source:string, config:array<string,mixed>}> $layers
     */
    public function load(array $layers): LayeredConfigResult
    {
        $merged = [];
        $sources = [];

        foreach ($layers as $layer) {
            $source = (string) ($layer['source'] ?? 'unknown');
            $config = $layer['config'] ?? [];

            if (! is_array($config)) {
                throw new \InvalidArgumentException('Layered config layer must contain an array config for source: ' . $source);
            }

            $merged = self::merge($merged, $config);
            $sources[] = $source;
        }

        return new LayeredConfigResult($merged, $sources);
    }

    /**
     * @param array<string,mixed> $base
     * @param array<string,mixed> $override
     * @return array<string,mixed>
     */
    private static function merge(array $base, array $override): array
    {
        foreach ($override as $key => $value) {
            if (
                array_key_exists($key, $base)
                && is_array($base[$key])
                && is_array($value)
                && self::isAssociative($base[$key])
                && self::isAssociative($value)
            ) {
                $base[$key] = self::merge($base[$key], $value);
                continue;
            }

            $base[$key] = $value;
        }

        return $base;
    }

    /** @param array<mixed> $value */
    private static function isAssociative(array $value): bool
    {
        return array_keys($value) !== range(0, count($value) - 1);
    }
}
