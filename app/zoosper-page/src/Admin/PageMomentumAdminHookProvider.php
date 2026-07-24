<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Provides a stable hook payload for future admin route/menu aggregation.
 *
 * This provider does not register routes or menu items. It converts the bridge
 * export into a simple payload a later runtime aggregator adapter can consume.
 */
final class PageMomentumAdminHookProvider
{
    /**
     * @param array<string, mixed> $bridgeExport
     * @return array<string, mixed>
     */
    public function payload(array $bridgeExport): array
    {
        $routes = isset($bridgeExport['routes']) && is_array($bridgeExport['routes'])
            ? array_values(array_filter($bridgeExport['routes'], 'is_array'))
            : [];
        $menuItems = isset($bridgeExport['menuItems']) && is_array($bridgeExport['menuItems'])
            ? array_values(array_filter($bridgeExport['menuItems'], 'is_array'))
            : [];

        $enabled = count($routes) === 1
            && count($menuItems) === 1
            && ($routes[0]['name'] ?? '') === 'admin.page_momentum.index'
            && ($menuItems[0]['route'] ?? '') === 'admin.page_momentum.index'
            && ($bridgeExport['liveMutation'] ?? true) === false;

        return [
            'page_momentum_admin_hook' => [
                'enabled' => $enabled,
                'routes' => $routes,
                'menu_items' => $menuItems,
                'source' => 'PageMomentumAdminAggregationBridge',
                'live_mutation' => false,
                'rollback' => [
                    'remove hook-consumer adapter from the next runtime patch',
                    'remove app/zoosper-page/config/admin_page_momentum_hook_candidate.php if needed',
                    'set page momentum metadata flags to false only if activation must be reverted',
                    'run Pest and inspect nginx/application logs',
                ],
            ],
        ];
    }
}
