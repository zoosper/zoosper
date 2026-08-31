<?php

declare(strict_types=1);

namespace Zoosper\Settings\Value;

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Settings\Definition\SettingDefinition;

/**
 * Resolves safe read-only catalogue values.
 *
 * Phase 9B intentionally distinguishes defaults from project/runtime config.
 * Environment/database/inheritance provenance is reserved for the scoped
 * persistence adapter introduced in the next phase.
 */
final readonly class SettingValueResolver
{
    public function __construct(private ConfigRepository $config)
    {
    }

    public function resolve(SettingDefinition $definition): SettingValue
    {
        $missing = new \stdClass();
        $configured = $this->config->get($definition->path, $missing);
        $hasConfiguredValue = $configured !== $missing;

        if ($definition->secret) {
            return new SettingValue(
                path: $definition->path,
                value: null,
                source: $hasConfiguredValue ? 'project' : ($definition->default !== null ? 'default' : 'unset'),
                readOnly: true,
                secret: true,
                explanation: 'Secret value is redacted and cannot be read from the catalogue.',
            );
        }

        if ($hasConfiguredValue) {
            return new SettingValue(
                path: $definition->path,
                value: $configured,
                source: 'project',
                readOnly: true,
                explanation: 'Controlled by project or runtime configuration.',
            );
        }

        if ($definition->default !== null) {
            return new SettingValue(
                path: $definition->path,
                value: $definition->default,
                source: 'default',
                readOnly: $definition->readOnly,
                explanation: 'Using the module-defined default value.',
            );
        }

        return new SettingValue(
            path: $definition->path,
            value: null,
            source: 'unset',
            readOnly: $definition->readOnly,
            explanation: 'No effective value is currently configured.',
        );
    }
}










