<?php

declare(strict_types=1);

namespace Zoosper\Core\Config;

/**
 * Loads named PHP config files and merges them through LayeredConfigLoader.
 */
final class ConfigFileLayeredLoader
{
    public function __construct(private LayeredConfigLoader $loader = new LayeredConfigLoader())
    {
    }

    /**
     * @param list<ConfigLayerSource> $sources
     */
    public function load(array $sources): LayeredConfigResult
    {
        $layers = [];
        foreach ($sources as $source) {
            if (! $source instanceof ConfigLayerSource) {
                throw new \InvalidArgumentException('Config file layered loader expects ConfigLayerSource instances.');
            }

            if (! is_file($source->path)) {
                continue;
            }

            $config = require $source->path;
            if (! is_array($config)) {
                throw new \UnexpectedValueException('Config file did not return an array: ' . $source->path);
            }

            $layers[] = [
                'source' => $source->source,
                'config' => $config,
            ];
        }

        return $this->loader->load($layers);
    }
}
