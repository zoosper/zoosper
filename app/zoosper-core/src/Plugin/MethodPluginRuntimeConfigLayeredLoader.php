<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

use ReflectionClass;
use RuntimeException;
use Zoosper\Core\Config\ConfigFileLayeredLoader;
use Zoosper\Core\Config\ConfigLayerSource;
use Zoosper\Core\Config\LayeredConfigResult;

/**
 * Loads method plugin runtime config through the core layered config loader.
 */
final readonly class MethodPluginRuntimeConfigLayeredLoader
{
    public function __construct(
        private ConfigFileLayeredLoader $layeredLoader = new ConfigFileLayeredLoader(),
        private MethodPluginRuntimeConfigLoader $configLoader = new MethodPluginRuntimeConfigLoader(),
    ) {
    }

    /**
     * @param array<string, string>|list<string> $sources source name => config path, or list of config paths
     */
    public function load(array $sources): MethodPluginRuntimeConfig
    {
        $layerSources = [];

        foreach ($sources as $source => $path) {
            if (!is_string($path) || $path === '') {
                throw new RuntimeException('Method plugin runtime config source path must be a non-empty string.');
            }

            $sourceName = is_string($source) ? $source : 'source:' . count($layerSources);
            $layerSources[] = new ConfigLayerSource($sourceName, $path);
        }

        return $this->configLoader->load($this->extractConfig($this->layeredLoader->load($layerSources)));
    }

    /**
     * @return array<string, mixed>
     */
    private function extractConfig(LayeredConfigResult $result): array
    {
        if (!property_exists($result, 'config')) {
            throw new RuntimeException('LayeredConfigResult did not expose a config payload.');
        }

        $property = (new ReflectionClass($result))->getProperty('config');
        $config = $property->getValue($result);

        if (!is_array($config)) {
            throw new RuntimeException('LayeredConfigResult config payload must be an array.');
        }

        /** @var array<string, mixed> $config */
        return $config;
    }
}
