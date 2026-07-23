<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

/**
 * Validates method plugin config before runtime execution is enabled.
 */
final class MethodPluginConfigValidator
{
    /**
     * @param array<string, mixed> $config
     */
    public function validateConfig(array $config): MethodPluginValidationResult
    {
        $plugins = $config['plugins'] ?? $config;
        $issues = [];

        if (!is_array($plugins)) {
            return new MethodPluginValidationResult([
                new MethodPluginValidationIssue('plugins_not_array', 'Method plugin config must be an array.'),
            ]);
        }

        foreach (array_values($plugins) as $index => $entry) {
            if (!is_array($entry)) {
                $issues[] = new MethodPluginValidationIssue('entry_not_array', 'Method plugin config entry must be an array.', ['index' => $index]);
                continue;
            }

            foreach (['subject', 'method', 'plugin'] as $required) {
                if (!isset($entry[$required]) || !is_string($entry[$required]) || $entry[$required] === '') {
                    $issues[] = new MethodPluginValidationIssue(
                        code: 'missing_' . $required,
                        message: sprintf('Method plugin config entry requires non-empty %s.', $required),
                        details: ['index' => $index, 'field' => $required],
                    );
                }
            }

            if (isset($entry['plugin']) && is_string($entry['plugin']) && $entry['plugin'] !== '') {
                $pluginClass = $entry['plugin'];

                if (!class_exists($pluginClass)) {
                    $issues[] = new MethodPluginValidationIssue(
                        code: 'plugin_class_missing',
                        message: 'Method plugin class does not exist: ' . $pluginClass,
                        details: ['index' => $index, 'pluginClass' => $pluginClass],
                    );
                    continue;
                }

                if (!is_subclass_of($pluginClass, MethodInterceptorInterface::class)) {
                    $issues[] = new MethodPluginValidationIssue(
                        code: 'plugin_interface_mismatch',
                        message: 'Method plugin class must implement ' . MethodInterceptorInterface::class . ': ' . $pluginClass,
                        details: ['index' => $index, 'pluginClass' => $pluginClass],
                    );
                }
            }
        }

        return new MethodPluginValidationResult($issues);
    }

    /**
     * @param list<MethodPluginDefinition> $definitions
     */
    public function validateDefinitions(array $definitions): MethodPluginValidationResult
    {
        $issues = [];

        foreach ($definitions as $index => $definition) {
            if (!$definition instanceof MethodPluginDefinition) {
                $issues[] = new MethodPluginValidationIssue('definition_type_mismatch', 'Expected MethodPluginDefinition instance.', ['index' => $index]);
                continue;
            }

            if (!class_exists($definition->pluginClass)) {
                $issues[] = new MethodPluginValidationIssue('plugin_class_missing', 'Method plugin class does not exist: ' . $definition->pluginClass, ['index' => $index, 'pluginClass' => $definition->pluginClass]);
                continue;
            }

            if (!is_subclass_of($definition->pluginClass, MethodInterceptorInterface::class)) {
                $issues[] = new MethodPluginValidationIssue('plugin_interface_mismatch', 'Method plugin class must implement ' . MethodInterceptorInterface::class . ': ' . $definition->pluginClass, ['index' => $index, 'pluginClass' => $definition->pluginClass]);
            }
        }

        return new MethodPluginValidationResult($issues);
    }
}
