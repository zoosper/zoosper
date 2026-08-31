<?php

declare(strict_types=1);

namespace Zoosper\Settings\Admin;

use Zoosper\Settings\Definition\SettingDefinition;
use Zoosper\Settings\Definition\SettingsSection;
use Zoosper\Settings\Value\SettingValue;

/** Prepares stable, value-safe metadata for the Settings Admin template. */
final readonly class SettingsPresentationBuilder
{
    private const CATEGORY_LABELS = [
        'general' => 'General',
        'communication' => 'Communication',
        'content' => 'Content',
        'design' => 'Design',
        'commerce' => 'Commerce',
        'security' => 'Security',
        'advanced' => 'Advanced',
    ];

    private const CATEGORY_DESCRIPTIONS = [
        'general' => 'Site-wide defaults and foundational behaviour.',
        'communication' => 'Email delivery, notifications and outbound communication.',
        'content' => 'Content authoring, pages and publishing behaviour.',
        'design' => 'Themes, templates and presentation.',
        'commerce' => 'Orders, catalogues and transactional behaviour.',
        'security' => 'Authentication, privacy and protection controls.',
        'advanced' => 'Administration and technical platform behaviour.',
    ];

    private const CATEGORY_ORDER = ['general', 'communication', 'content', 'design', 'commerce', 'security', 'advanced'];

    /**
     * @param list<SettingsSection> $sections
     * @param array<string, SettingValue> $effectiveValues
     * @param list<string> $websiteOptions
     * @param list<string> $storeOptions
     * @param array<int, object> $siteOptions
     * @return array<string, mixed>
     */
    public function build(array $sections, array $effectiveValues, array $websiteOptions, array $storeOptions, array $siteOptions): array
    {
        $categories = [];
        $modules = [];
        $sectionPresentation = [];
        $fieldPresentation = [];

        foreach ($sections as $section) {
            $categories[$section->category][] = $section;
            $modules[$section->module] = $section->module;
            $editableCount = 0;

            foreach ($section->settings as $setting) {
                $effective = $effectiveValues[$setting->path];
                $field = $this->field($setting, $effective);
                $fieldPresentation[$setting->path] = $field;
                $editableCount += $field['editable'] ? 1 : 0;
            }

            $sectionPresentation[$section->id] = [
                'editable' => $editableCount > 0,
                'search' => $this->sectionSearch($section),
            ];
        }

        uksort($categories, static function (string $left, string $right): int {
            $leftIndex = array_search($left, self::CATEGORY_ORDER, true);
            $rightIndex = array_search($right, self::CATEGORY_ORDER, true);
            return [is_int($leftIndex) ? $leftIndex : 999, $left]
                <=> [is_int($rightIndex) ? $rightIndex : 999, $right];
        });
        ksort($modules);

        return [
            'categories' => $categories,
            'categoryLabels' => self::CATEGORY_LABELS,
            'categoryDescriptions' => self::CATEGORY_DESCRIPTIONS,
            'firstCategory' => (string) (array_key_first($categories) ?? 'advanced'),
            'settingsModules' => $modules,
            'sectionPresentation' => $sectionPresentation,
            'fieldPresentation' => $fieldPresentation,
            'scopeOptionsJson' => $this->scopeOptionsJson($websiteOptions, $storeOptions, $siteOptions),
        ];
    }

    /** @return array<string, mixed> */
    private function field(SettingDefinition $setting, SettingValue $effective): array
    {
        $editable = !$setting->readOnly && !$setting->secret && $effective->source !== 'project';
        $value = $effective->value;
        $selectedValues = [];
        if ($setting->type === 'multiselect' && is_string($value)) {
            $decoded = json_decode($value, true);
            $selectedValues = is_array($decoded) ? array_map('strval', $decoded) : [];
        }

        return [
            'id' => 'setting-' . str_replace(['.', '_'], '-', $setting->path),
            'editable' => $editable,
            'inputName' => 'settings[' . $setting->path . ']',
            'inputType' => match ($setting->type) {
                'email' => 'email',
                'url' => 'url',
                'integer', 'decimal' => 'number',
                default => 'text',
            },
            'step' => match ($setting->type) {
                'integer' => '1',
                'decimal' => 'any',
                default => '',
            },
            'inputValue' => $value === null ? '' : (string) $value,
            'displayValue' => $effective->secret
                ? '••••••••'
                : ($value === null ? 'Not set' : (is_bool($value) ? ($value ? 'Enabled' : 'Disabled') : (string) $value)),
            'checked' => $value === true || in_array((string) $value, ['1', 'true'], true),
            'selectedValues' => $selectedValues,
        ];
    }

    private function sectionSearch(SettingsSection $section): string
    {
        $tokens = [$section->category, $section->id, $section->label, $section->description, $section->module];
        foreach ($section->groups as $group) {
            $tokens[] = $group->label;
            $tokens[] = $group->description;
            foreach ($group->settings as $setting) {
                array_push($tokens, $setting->path, $setting->label, $setting->description);
            }
        }
        return strtolower(implode(' ', $tokens));
    }

    /** @param list<string> $websites @param list<string> $stores @param array<int, object> $sites */
    private function scopeOptionsJson(array $websites, array $stores, array $sites): string
    {
        $siteOptions = [];
        foreach ($sites as $site) {
            $siteOptions[(string) $site->id] = $site->name;
        }
        return json_encode([
            'website' => array_combine($websites, $websites) ?: [],
            'store' => array_combine($stores, $stores) ?: [],
            'site' => $siteOptions,
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: '{}';
    }
}










