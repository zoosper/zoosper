<?php

declare(strict_types=1);

namespace Zoosper\Admin\Navigation;

use Marko\Admin\Contracts\AdminSectionInterface;
use Marko\Admin\Contracts\MenuItemInterface;

/** Builds Marko sections from flat items plus mergeable module section metadata. */
final readonly class AdminSectionBuilder
{
    public function __construct(private ?AdminSectionMetadataLoader $metadata = null)
    {
    }

    /** @param list<MenuItemInterface> $items @return list<AdminSectionInterface> */
    public function build(array $items, ?array $sectionMetadata = null): array
    {
        /** @var array<string, list<MenuItemInterface>> $groups */
        $groups = [];
        /** @var array<string, string> $fallbackLabels */
        $fallbackLabels = [];
        foreach ($items as $item) {
            $label = $item instanceof AdminMenuItem ? $item->group : 'Main';
            $id = $this->normaliseId($label);
            $id = $id !== '' ? $id : 'main';
            $groups[$id][] = $item;
            $fallbackLabels[$id] = $label;
        }

        /** @var array<string, AdminSectionMetadata> $metadata */
        $metadata = $sectionMetadata ?? $this->metadata?->load() ?? [];
        $registry = new AdminSectionRegistry();
        $fallbackOrder = 1000;
        foreach ($groups as $id => $groupItems) {
            $definition = $metadata[$id] ?? new AdminSectionMetadata(
                $id,
                $fallbackLabels[$id],
                '',
                $fallbackOrder,
            );
            $registry->register(new AdminSection(
                $definition->id,
                $definition->label,
                $groupItems,
                $definition->icon,
                $definition->sortOrder,
            ));
            $fallbackOrder += 10;
        }

        return $registry->all();
    }

    private function normaliseId(string $value): string
    {
        return trim(strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', trim($value))), '-');
    }
}
