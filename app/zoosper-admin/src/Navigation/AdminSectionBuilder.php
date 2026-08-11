<?php

declare(strict_types=1);

namespace Zoosper\Admin\Navigation;

use Marko\Admin\Contracts\AdminSectionInterface;
use Marko\Admin\Contracts\MenuItemInterface;

/** Builds Marko sections from the existing module-owned flat menu declarations. */
final readonly class AdminSectionBuilder
{
    /** @param list<MenuItemInterface> $items @return list<AdminSectionInterface> */
    public function build(array $items): array
    {
        /** @var array<string, list<MenuItemInterface>> $groups */
        $groups = [];
        foreach ($items as $item) {
            $group = $item instanceof AdminMenuItem ? $item->group : 'main';
            $groups[$group][] = $item;
        }

        $registry = new AdminSectionRegistry();
        $position = 10;
        foreach ($groups as $label => $groupItems) {
            $id = strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', trim($label)));
            $registry->register(new AdminSection(
                $id !== '' ? trim($id, '-') : 'main',
                $label,
                $groupItems,
                sortOrder: $position,
            ));
            $position += 10;
        }

        return $registry->all();
    }
}
