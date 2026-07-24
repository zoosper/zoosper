<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Converts the isolated Page Momentum candidate into admin menu definitions.
 *
 * This bridge performs no registration. It only returns a normalised menu item
 * list for a future admin menu aggregator hook.
 */
final class PageMomentumAdminMenuBridge
{
    /**
     * @param array<string, mixed> $candidate
     * @return list<array<string, mixed>>
     */
    public function items(array $candidate): array
    {
        $root = $candidate['page_momentum_admin_integration'] ?? [];
        if (!is_array($root) || ($root['enabled'] ?? false) !== true) {
            return [];
        }

        $items = isset($root['menu_items']) && is_array($root['menu_items']) ? $root['menu_items'] : [];

        return array_values(array_filter(
            $items,
            static fn (mixed $item): bool => is_array($item)
                && ($item['route'] ?? '') === 'admin.page_momentum.index'
                && ($item['permission'] ?? '') === 'page.manage'
                && isset($item['label'])
        ));
    }
}
