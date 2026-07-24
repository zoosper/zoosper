<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

/**
 * Converts method plugin runtime config arrays into MethodPluginRuntimeConfig.
 */
final class MethodPluginRuntimeConfigLoader
{
    /**
     * @param array<string, mixed> $config
     */
    public function load(array $config): MethodPluginRuntimeConfig
    {
        $runtime = $config['method_plugins'] ?? $config['method_plugin_runtime'] ?? [];

        if (!is_array($runtime)) {
            return MethodPluginRuntimeConfig::disabled();
        }

        $enabled = isset($runtime['enabled']) && (bool) $runtime['enabled'];
        $reportOnly = !array_key_exists('report_only', $runtime) || (bool) $runtime['report_only'];
        $allowList = $runtime['allow_list'] ?? [];

        if (!is_array($allowList)) {
            $allowList = [];
        }

        $allowList = array_values(array_filter(
            $allowList,
            static fn (mixed $value): bool => is_string($value) && $value !== ''
        ));

        // Hard safety rule: disabled runtime always resolves to an empty allow-list,
        // even if module defaults provided candidate entries before root/project override.
        if (!$enabled) {
            $allowList = [];
        }

        return new MethodPluginRuntimeConfig(
            enabled: $enabled,
            reportOnly: $reportOnly,
            reportOnlyInvocationKeys: $allowList,
        );
    }
}
