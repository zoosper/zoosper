<?php

declare(strict_types=1);

namespace Zoosper\Settings\Definition;

use InvalidArgumentException;

/** One collapsible, module-owned group of related settings inside a section. */
final readonly class SettingsGroup
{
    /** @param list<SettingDefinition> $settings */
    public function __construct(
        public string $id,
        public string $label,
        public array $settings,
        public string $description = '',
        public int $sortOrder = 100,
        public bool $openByDefault = false,
    ) {
        if ($id === '' || !preg_match('/^[a-z][a-z0-9_.-]*$/', $id)) {
            throw new InvalidArgumentException("Invalid settings group id: {$id}");
        }
        if ($label === '') {
            throw new InvalidArgumentException("Settings group label is required for: {$id}");
        }
        foreach ($settings as $setting) {
            if (!$setting instanceof SettingDefinition) {
                throw new InvalidArgumentException("Settings group '{$id}' contains an invalid definition.");
            }
        }
    }
}
