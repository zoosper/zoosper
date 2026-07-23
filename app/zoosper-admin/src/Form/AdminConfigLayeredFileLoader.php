<?php

declare(strict_types=1);

namespace Zoosper\Admin\Form;

use ReflectionClass;
use RuntimeException;
use Zoosper\Core\Config\ConfigFileLayeredLoader;
use Zoosper\Core\Config\ConfigLayerSource;
use Zoosper\Core\Config\LayeredConfigResult;

/**
 * Admin-facing bridge for layered PHP config files.
 *
 * Modules pass their default config files first; root/project overrides are
 * passed later. The underlying ConfigFileLayeredLoader performs the actual
 * layered merge and returns a LayeredConfigResult.
 */
final class AdminConfigLayeredFileLoader
{
    public function __construct(
        private readonly ConfigFileLayeredLoader $loader = new ConfigFileLayeredLoader(),
    ) {
    }

    /**
     * Load ordered config sources and return the merged config payload.
     *
     * Example:
     *
     * ```php
     * $loader->load([
     *     'module:zoosper-page' => '/path/to/app/zoosper-page/config/admin_forms.php',
     *     'root:admin_forms' => '/path/to/config/admin_forms.php',
     * ]);
     * ```
     *
     * @param array<string, string>|list<string> $sources Map of source name to file path, or list of file paths.
     * @return array<string, mixed>
     */
    public function load(array $sources): array
    {
        $layerSources = [];

        foreach ($sources as $source => $path) {
            if (!is_string($path) || $path === '') {
                throw new RuntimeException('Admin config layered file loader expects non-empty file paths.');
            }

            $sourceName = is_string($source) ? $source : 'source:' . count($layerSources);
            $layerSources[] = new ConfigLayerSource($sourceName, $path);
        }

        return $this->extractConfig($this->loader->load($layerSources));
    }

    /**
     * @return array<string, mixed>
     */
    private function extractConfig(LayeredConfigResult $result): array
    {
        if (property_exists($result, 'config')) {
            $reflection = new ReflectionClass($result);
            $property = $reflection->getProperty('config');
            $config = $property->getValue($result);

            if (is_array($config)) {
                /** @var array<string, mixed> $config */
                return $config;
            }
        }

        throw new RuntimeException('LayeredConfigResult did not expose an array config payload.');
    }
}
