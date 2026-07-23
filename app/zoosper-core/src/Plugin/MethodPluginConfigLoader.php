<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

use RuntimeException;

/**
 * Converts PHP array config into MethodPluginDefinition objects.
 */
final class MethodPluginConfigLoader
{
    /**
     * @param array<string, mixed> $config
     * @return list<MethodPluginDefinition>
     */
    public function load(array $config): array
    {
        $plugins = $config['plugins'] ?? $config;

        if (!is_array($plugins)) {
            throw new RuntimeException('Method plugin config must be an array.');
        }

        $definitions = [];

        foreach ($plugins as $entry) {
            if (!is_array($entry)) {
                throw new RuntimeException('Each method plugin config entry must be an array.');
            }

            foreach (['subject', 'method', 'plugin'] as $required) {
                if (!isset($entry[$required]) || !is_string($entry[$required]) || $entry[$required] === '') {
                    throw new RuntimeException(sprintf('Method plugin config entry requires non-empty %s.', $required));
                }
            }

            $definitions[] = new MethodPluginDefinition(
                subject: $entry['subject'],
                method: $entry['method'],
                pluginClass: $entry['plugin'],
                sortOrder: isset($entry['sortOrder']) ? (int) $entry['sortOrder'] : 100,
                enabled: !array_key_exists('enabled', $entry) || (bool) $entry['enabled'],
            );
        }

        return $definitions;
    }
}
