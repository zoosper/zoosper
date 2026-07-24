<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Read-only preview of consuming PageMomentumAdminRouteMenuHook from the real
 * admin route/menu aggregation source.
 *
 * This class validates the precise output that a future source patch should
 * append. It does not register routes or menu entries.
 */
final class PageMomentumAdminRouteMenuHookConsumerPreview
{
    /**
     * @param array<string, mixed> $hookExport
     * @param array<string, mixed> $discovery
     * @return array<string, mixed>
     */
    public function preview(array $hookExport, array $discovery = []): array
    {
        $routes = isset($hookExport['routes']) && is_array($hookExport['routes'])
            ? array_values(array_filter($hookExport['routes'], 'is_array'))
            : [];
        $menuItems = isset($hookExport['menuItems']) && is_array($hookExport['menuItems'])
            ? array_values(array_filter($hookExport['menuItems'], 'is_array'))
            : [];
        $routeFiles = isset($discovery['routeFiles']) && is_array($discovery['routeFiles']) ? $discovery['routeFiles'] : [];
        $menuFiles = isset($discovery['menuFiles']) && is_array($discovery['menuFiles']) ? $discovery['menuFiles'] : [];

        $route = is_array($routes[0] ?? null) ? $routes[0] : [];
        $menu = is_array($menuItems[0] ?? null) ? $menuItems[0] : [];

        $checks = [
            'single_route' => count($routes) === 1,
            'single_menu_item' => count($menuItems) === 1,
            'route_name_ok' => ($route['name'] ?? '') === 'admin.page_momentum.index',
            'route_method_get' => ($route['method'] ?? '') === 'GET',
            'route_path_ok' => ($route['path'] ?? '') === '/admin/page-momentum',
            'route_controller_present' => isset($route['controller']) && is_string($route['controller']) && $route['controller'] !== '',
            'route_action_index' => ($route['action'] ?? '') === 'index',
            'route_permission_page_manage' => ($route['permission'] ?? '') === 'page.manage',
            'menu_route_matches' => ($menu['route'] ?? '') === 'admin.page_momentum.index',
            'menu_permission_page_manage' => ($menu['permission'] ?? '') === 'page.manage',
            'hook_live_mutation_false' => ($hookExport['liveMutation'] ?? true) === false,
        ];

        return [
            'readyForConsumerPatch' => !in_array(false, $checks, true),
            'checks' => $checks,
            'routes' => $routes,
            'menuItems' => $menuItems,
            'routeCount' => count($routes),
            'menuCount' => count($menuItems),
            'routeFilesDiscovered' => array_values($routeFiles),
            'menuFilesDiscovered' => array_values($menuFiles),
            'recommendedPatch' => [
                'inspect the discovered admin route/menu aggregation files before editing',
                'instantiate or load PageMomentumAdminRouteMenuHook from the page module',
                'call routes() and append the single route only if admin.page_momentum.index is not already registered',
                'call menuItems() and append the single menu item only if it is not already registered',
                'guard route and menu with page.manage',
                'smoke /admin and /admin/page-momentum, run Pest, and inspect logs',
            ],
            'rollback' => [
                'remove the route/menu hook consumer code added in the next phase',
                'leave the passive hook/config in place unless diagnosis requires removal',
                'if runtime smoke fails, set page momentum metadata enabled flags to false',
                'rerun Pest and inspect nginx/application logs',
            ],
            'liveMutation' => false,
        ];
    }
}
