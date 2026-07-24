<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Read-only preview of consuming the generated Page Momentum hook candidate.
 *
 * This class validates the candidate payload shape that a future admin route/menu
 * aggregator source patch should consume. It does not register routes or menu
 * items.
 */
final class PageMomentumAdminHookConsumerPreview
{
    /**
     * @param array<string, mixed> $hookCandidate
     * @return array<string, mixed>
     */
    public function preview(array $hookCandidate): array
    {
        $root = $hookCandidate['page_momentum_admin_hook'] ?? [];
        $routes = is_array($root) && isset($root['routes']) && is_array($root['routes'])
            ? array_values(array_filter($root['routes'], 'is_array'))
            : [];
        $menuItems = is_array($root) && isset($root['menu_items']) && is_array($root['menu_items'])
            ? array_values(array_filter($root['menu_items'], 'is_array'))
            : [];

        $route = is_array($routes[0] ?? null) ? $routes[0] : [];
        $menuItem = is_array($menuItems[0] ?? null) ? $menuItems[0] : [];

        $checks = [
            'hook_enabled' => is_array($root) && ($root['enabled'] ?? false) === true,
            'hook_live_mutation_false' => is_array($root) && ($root['live_mutation'] ?? true) === false,
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
            'rollback_present' => is_array($root) && isset($root['rollback']) && is_array($root['rollback']) && count($root['rollback']) > 0,
        ];

        return [
            'readyForSourceHook' => !in_array(false, $checks, true),
            'checks' => $checks,
            'routes' => $routes,
            'menuItems' => $menuItems,
            'routeCount' => count($routes),
            'menuCount' => count($menuItems),
            'liveMutation' => false,
        ];
    }
}
