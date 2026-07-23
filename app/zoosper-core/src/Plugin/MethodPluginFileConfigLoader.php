<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

use RuntimeException;

/**
 * Loads method plugin definitions from PHP config files.
 */
final readonly class MethodPluginFileConfigLoader
{
    public function __construct(
        private MethodPluginConfigLoader $configLoader = new MethodPluginConfigLoader(),
    ) {
    }

    /**
     * @param list<string> $files
     * @return list<MethodPluginDefinition>
     */
    public function loadFiles(array $files): array
    {
        $definitions = [];

        foreach ($files as $file) {
            if (!is_file($file)) {
                throw new RuntimeException(sprintf('Method plugin config file does not exist: %s', $file));
            }

            $config = require $file;

            if (!is_array($config)) {
                throw new RuntimeException(sprintf('Method plugin config file must return an array: %s', $file));
            }

            foreach ($this->configLoader->load($config) as $definition) {
                $definitions[] = $definition;
            }
        }

        return $definitions;
    }
}
