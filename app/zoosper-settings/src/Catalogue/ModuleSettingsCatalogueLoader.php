<?php

declare(strict_types=1);

namespace Zoosper\Settings\Catalogue;

use RuntimeException;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Settings\Definition\SettingDefinition;
use Zoosper\Settings\Definition\SettingsGroup;
use Zoosper\Settings\Definition\SettingsSection;

/** Loads module-owned config/admin_settings.php metadata. */
final readonly class ModuleSettingsCatalogueLoader
{
    public function __construct(private ModuleRegistry $modules)
    {
    }

    public function load(): SettingsCatalogue
    {
        $sections = [];
        $ids = [];
        foreach ($this->modules->enabledModules() as $module) {
            $file = $module->configPath('admin_settings.php');
            if (!is_file($file)) {
                continue;
            }
            $raw = require $file;
            if (!is_array($raw)) {
                throw new RuntimeException("Admin settings config must return an array: {$file}");
            }
            foreach ($raw as $section) {
                $definition = $this->section($section, $module->name, $file);
                if (isset($ids[$definition->id])) {
                    throw new RuntimeException("Duplicate admin settings section id '{$definition->id}' in {$file}");
                }
                $ids[$definition->id] = true;
                $sections[] = $definition;
            }
        }
        return new SettingsCatalogue($sections);
    }

    /** @param mixed $raw */
    private function section(mixed $raw, string $module, string $file): SettingsSection
    {
        if (!is_array($raw)) {
            throw new RuntimeException("Invalid admin settings section in: {$file}");
        }
        if (isset($raw['groups']) && isset($raw['settings'])) {
            throw new RuntimeException("Settings section must declare groups or settings, not both: {$file}");
        }

        $groups = [];
        if (isset($raw['groups'])) {
            foreach ((array) $raw['groups'] as $group) {
                $groups[] = $this->group($group, $file);
            }
        } else {
            $groups[] = new SettingsGroup(
                id: 'general',
                label: 'General',
                settings: $this->settings((array) ($raw['settings'] ?? []), $file),
                openByDefault: true,
            );
        }
        usort($groups, static fn (SettingsGroup $a, SettingsGroup $b): int => [$a->sortOrder, $a->label] <=> [$b->sortOrder, $b->label]);

        $settings = [];
        $paths = [];
        foreach ($groups as $group) {
            foreach ($group->settings as $setting) {
                if (isset($paths[$setting->path])) {
                    throw new RuntimeException("Duplicate setting path '{$setting->path}' in section '{$raw['id']}'");
                }
                $paths[$setting->path] = true;
                $settings[] = $setting;
            }
        }

        return new SettingsSection(
            id: (string) ($raw['id'] ?? ''),
            label: (string) ($raw['label'] ?? ''),
            category: (string) ($raw['category'] ?? 'advanced'),
            module: $module,
            settings: $settings,
            description: (string) ($raw['description'] ?? ''),
            permission: (string) ($raw['permission'] ?? 'settings.manage'),
            sortOrder: (int) ($raw['sort_order'] ?? 100),
            groups: $groups,
        );
    }

    /** @param mixed $raw */
    private function group(mixed $raw, string $file): SettingsGroup
    {
        if (!is_array($raw)) {
            throw new RuntimeException("Invalid settings group in: {$file}");
        }
        return new SettingsGroup(
            id: (string) ($raw['id'] ?? ''),
            label: (string) ($raw['label'] ?? ''),
            settings: $this->settings((array) ($raw['settings'] ?? []), $file),
            description: (string) ($raw['description'] ?? ''),
            sortOrder: (int) ($raw['sort_order'] ?? 100),
            openByDefault: (bool) ($raw['open_by_default'] ?? false),
        );
    }

    /** @param array<int, mixed> $rawSettings @return list<SettingDefinition> */
    private function settings(array $rawSettings, string $file): array
    {
        $settings = [];
        foreach ($rawSettings as $setting) {
            if (!is_array($setting)) {
                throw new RuntimeException("Invalid setting definition in: {$file}");
            }
            $settings[] = new SettingDefinition(
                path: (string) ($setting['path'] ?? ''),
                label: (string) ($setting['label'] ?? ''),
                type: (string) ($setting['type'] ?? 'text'),
                description: (string) ($setting['description'] ?? ''),
                default: $setting['default'] ?? null,
                options: array_values(array_map('strval', (array) ($setting['options'] ?? []))),
                secret: (bool) ($setting['secret'] ?? false),
                readOnly: (bool) ($setting['read_only'] ?? false),
                sortOrder: (int) ($setting['sort_order'] ?? 100),
            );
        }
        usort($settings, static fn (SettingDefinition $a, SettingDefinition $b): int => [$a->sortOrder, $a->label] <=> [$b->sortOrder, $b->label]);
        return $settings;
    }
}










