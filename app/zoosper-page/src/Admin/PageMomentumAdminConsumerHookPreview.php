<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Read-only preview of the future admin route/menu aggregation consumer hook.
 *
 * This class accepts the bridge export and validates the exact shape that a
 * later live aggregation hook should consume. It does not mutate router/menu
 * runtime state.
 */
final class PageMomentumAdminConsumerHookPreview
{
    /**
     * @param array<string, mixed> $bridgeExport
     * @return array<string, mixed>
     */
    public function preview(array $bridgeExport): array
    {
        $routes = isset($bridgeExport['routes']) && is_array($bridgeExport['routes'])
            ? array_values(array_filter($bridgeExport['routes'], 'is_array'))
            : [];
        $menuItems = isset($bridgeExport['menuItems']) && is_array($bridgeExport['menuItems'])
            ? array_values(array_filter($bridgeExport['menuItems'], 'is_array'))
            : [];

        $route = is_array($routes[0] ?? null) ? $routes[0] : [];
        $menuItem = is_array($menuItems[0] ?? null) ? $menuItems[0] : [];

        $checks = [
            'single_route' => count($routes) === 1,
            'single_menu_item' => count($menuItems) === 1,
            'route_name_ok' => ($route['name'] ?? '') === 'admin.page_momentum.index',
            'route_method_get' => ($route['method'] ?? '') === 'GET',
            'route_path_ok' => ($route['path'] ?? '') === '/admin/page-momentum',
            'route_controller_present' => isset($route['controller']) && is_string($route['controller']) && $route['controller'] !== '',
            'route_action_index' => ($route['action'] ?? '') === 'index',
            'route_permission_page_manage' => ($route['permission'] ?? '') === 'page.manage',
            'menu_route_matches' => ($menuItem['route'] ?? '') === 'admin.page_momentum.index',
            'menu_permission_page_manage' => ($menuItem['permission'] ?? '') === 'page.manage',
            'bridge_live_mutation_false' => ($bridgeExport['liveMutation'] ?? true) === false,
        ];

        return [
            'readyForLiveHook' => !in_array(false, $checks, true),
            'checks' => $checks,
            'routes' => $routes,
            'menuItems' => $menuItems,
            'routeCount' => count($routes),
            'menuCount' => count($menuItems),
            'liveMutation' => false,
        ];
    }
}
