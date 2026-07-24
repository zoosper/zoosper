<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Builds an isolated route/menu candidate payload for the Page Momentum panel.
 *
 * This builder does not mutate existing route/menu aggregators. It converts the
 * active metadata into a dedicated candidate config that a later phase can wire
 * into the real admin router/menu pipeline.
 */
final class PageMomentumAggregatorPatchBuilder
{
    /**
     * @param array<string, mixed> $routeConfig
     * @param array<string, mixed> $menuConfig
     * @return array<string, mixed>
     */
    public function buildCandidate(array $routeConfig, array $menuConfig): array
    {
        $routeRoot = $routeConfig['page_momentum_routes'] ?? [];
        $menuRoot = $menuConfig['page_momentum_menu'] ?? [];

        $routes = is_array($routeRoot) && isset($routeRoot['routes']) && is_array($routeRoot['routes'])
            ? array_values(array_filter($routeRoot['routes'], 'is_array'))
            : [];
        $items = is_array($menuRoot) && isset($menuRoot['items']) && is_array($menuRoot['items'])
            ? array_values(array_filter($menuRoot['items'], 'is_array'))
            : [];

        return [
            'page_momentum_admin_integration' => [
                'enabled' => ($routeRoot['enabled'] ?? false) === true && ($menuRoot['enabled'] ?? false) === true,
                'source' => 'page-momentum-metadata',
                'routes' => $routes,
                'menu_items' => $items,
                'live_mutation' => false,
                'rollback' => [
                    'remove this candidate config if runtime wiring fails',
                    'set page_momentum_routes.enabled to false if metadata needs rollback',
                    'set page_momentum_menu.enabled to false if metadata needs rollback',
                    'rerun Pest and inspect nginx/application logs',
                ],
            ],
        ];
    }
}
