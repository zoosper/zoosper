<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

use RuntimeException;

/**
 * Loads method plugin definitions from discovered module config sources.
 */
final readonly class MethodPluginModuleConfigLoader
{
    public function __construct(
        private MethodPluginFileConfigLoader $fileLoader = new MethodPluginFileConfigLoader(),
    ) {
    }

    /**
     * @param list<MethodPluginConfigSource> $sources
     * @return list<MethodPluginDefinition>
     */
    public function load(array $sources): array
    {
        $files = [];

        foreach ($sources as $source) {
            if (!$source instanceof MethodPluginConfigSource) {
                throw new RuntimeException('Method plugin module config loader expects MethodPluginConfigSource instances.');
            }

            $files[] = $source->path;
        }

        return $this->fileLoader->loadFiles($files);
    }
}
