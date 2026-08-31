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
            $structuredItems = $this->buildHierarchy($groupItems);
            if ($structuredItems === []) {
                continue;
            }

            $definition = $metadata[$id] ?? new AdminSectionMetadata(
                $id,
                $fallbackLabels[$id],
                '',
                $fallbackOrder,
            );
            $registry->register(new AdminSection(
                $definition->id,
                $definition->label,
                $structuredItems,
                $definition->icon,
                $definition->sortOrder,
            ));
            $fallbackOrder += 10;
        }

        return $registry->all();
    }

    /**
     * @param list<MenuItemInterface> $items
     * @return list<MenuItemInterface>
     */
    private function buildHierarchy(array $items): array
    {
        /** @var array<string, AdminMenuItem> $itemsByCode */
        $itemsByCode = [];
        /** @var list<MenuItemInterface> $nonAdminItems */
        $nonAdminItems = [];
        /** @var array<string, list<AdminMenuItem>> $childrenByParent */
        $childrenByParent = [];
        /** @var list<AdminMenuItem> $rootItems */
        $rootItems = [];

        foreach ($items as $item) {
            if ($item instanceof AdminMenuItem) {
                $itemsByCode[$item->code] = $item;
                if ($item->parent !== null && $item->parent !== '') {
                    $childrenByParent[$item->parent][] = $item;
                } else {
                    $rootItems[] = $item;
                }
            } else {
                $nonAdminItems[] = $item;
            }
        }

        $structuredRoots = [];
        foreach ($rootItems as $root) {
            $structuredRoots[] = $this->attachChildren($root, $childrenByParent);
        }

        foreach ($childrenByParent as $parentCode => $orphans) {
            if (!isset($itemsByCode[$parentCode])) {
                foreach ($orphans as $orphan) {
                    $structuredRoots[] = $this->attachChildren($orphan, $childrenByParent);
                }
            }
        }

        $allStructured = array_merge($structuredRoots, $nonAdminItems);
        usort(
            $allStructured,
            static fn (MenuItemInterface $a, MenuItemInterface $b): int => [$a->getSortOrder(), $a->getLabel(), $a->getId()]
                <=> [$b->getSortOrder(), $b->getLabel(), $b->getId()],
        );

        return $allStructured;
    }

    /**
     * @param array<string, list<AdminMenuItem>> $childrenByParent
     */
    private function attachChildren(AdminMenuItem $item, array &$childrenByParent): AdminMenuItem
    {
        $directChildren = $childrenByParent[$item->code] ?? [];
        unset($childrenByParent[$item->code]);

        $nestedChildren = [];
        foreach ($directChildren as $child) {
            $nestedChildren[] = $this->attachChildren($child, $childrenByParent);
        }

        foreach ($item->getChildren() as $existingChild) {
            if ($existingChild instanceof AdminMenuItem) {
                $nestedChildren[] = $this->attachChildren($existingChild, $childrenByParent);
            } else {
                $nestedChildren[] = $existingChild;
            }
        }

        usort(
            $nestedChildren,
            static fn (MenuItemInterface $a, MenuItemInterface $b): int => [$a->getSortOrder(), $a->getLabel(), $a->getId()]
                <=> [$b->getSortOrder(), $b->getLabel(), $b->getId()],
        );

        return $nestedChildren !== [] ? $item->withChildren($nestedChildren) : $item;
    }

    private function normaliseId(string $value): string
    {
        return trim(strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', trim($value))), '-');
    }
}










