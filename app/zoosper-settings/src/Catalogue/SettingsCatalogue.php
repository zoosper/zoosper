<?php

declare(strict_types=1);

namespace Zoosper\Settings\Catalogue;

use Zoosper\Settings\Definition\SettingsSection;

/** Sorted, searchable read-only settings metadata. */
final readonly class SettingsCatalogue
{
    /** @param list<SettingsSection> $sections */
    public function __construct(private array $sections)
    {
    }

    /** @return list<SettingsSection> */
    public function all(): array
    {
        $sections = $this->sections;
        usort($sections, static fn (SettingsSection $a, SettingsSection $b): int =>
            [$a->category, $a->sortOrder, $a->label, $a->id]
            <=> [$b->category, $b->sortOrder, $b->label, $b->id]
        );

        return $sections;
    }

    /** @return list<SettingsSection> */
    public function search(string $query): array
    {
        $needle = strtolower(trim($query));
        if ($needle === '') {
            return $this->all();
        }

        return array_values(array_filter($this->all(), static function (SettingsSection $section) use ($needle): bool {
            $haystack = [$section->id, $section->label, $section->description, $section->category, $section->module];
            foreach ($section->settings as $setting) {
                $haystack[] = $setting->path;
                $haystack[] = $setting->label;
                $haystack[] = $setting->description;
            }

            return str_contains(strtolower(implode(' ', $haystack)), $needle);
        }));
    }
}
