<?php

declare(strict_types=1);

namespace Zoosper\Settings\Definition;

use InvalidArgumentException;

/** One module-owned section in the Settings catalogue. */
final readonly class SettingsSection
{
    /**
     * @param list<SettingDefinition> $settings Flattened compatibility view.
     * @param list<SettingsGroup> $groups Organised group hierarchy.
     */
    public function __construct(
        public string $id,
        public string $label,
        public string $category,
        public string $module,
        public array $settings,
        public string $description = '',
        public string $permission = 'settings.manage',
        public int $sortOrder = 100,
        public array $groups = [],
    ) {
        if ($id === '' || !preg_match('/^[a-z][a-z0-9_.-]*$/', $id)) {
            throw new InvalidArgumentException("Invalid settings section id: {$id}");
        }
        if ($label === '' || $category === '' || $module === '') {
            throw new InvalidArgumentException("Settings section metadata is incomplete: {$id}");
        }
        foreach ($settings as $setting) {
            if (!$setting instanceof SettingDefinition) {
                throw new InvalidArgumentException("Settings section '{$id}' contains an invalid definition.");
            }
        }
        foreach ($groups as $group) {
            if (!$group instanceof SettingsGroup) {
                throw new InvalidArgumentException("Settings section '{$id}' contains an invalid group.");
            }
        }
        if ($groups !== [] && $this->flatten($groups) !== $settings) {
            throw new InvalidArgumentException("Settings section '{$id}' flattened settings do not match its groups.");
        }
    }

    /** @param list<SettingsGroup> $groups @return list<SettingDefinition> */
    private function flatten(array $groups): array
    {
        $settings = [];
        foreach ($groups as $group) {
            array_push($settings, ...$group->settings);
        }
        return $settings;
    }
}










