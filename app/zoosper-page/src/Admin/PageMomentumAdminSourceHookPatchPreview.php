<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Builds a source-level patch preview from the passive Page Momentum source hook adapter export.
 *
 * This is the final read-only proof before a later phase edits the real admin
 * route/menu aggregation source. It never registers routes or menu entries.
 */
final class PageMomentumAdminSourceHookPatchPreview
{
    /**
     * @param array<string, mixed> $adapterExport
     * @param array<string, mixed> $discovery
     * @return array<string, mixed>
     */
    public function preview(array $adapterExport, array $discovery = []): array
    {
        $routes = isset($adapterExport['routes']) && is_array($adapterExport['routes'])
            ? array_values(array_filter($adapterExport['routes'], 'is_array'))
            : [];
        $menuItems = isset($adapterExport['menuItems']) && is_array($adapterExport['menuItems'])
            ? array_values(array_filter($adapterExport['menuItems'], 'is_array'))
            : [];
        $routeFiles = isset($discovery['routeFiles']) && is_array($discovery['routeFiles']) ? $discovery['routeFiles'] : [];
        $menuFiles = isset($discovery['menuFiles']) && is_array($discovery['menuFiles']) ? $discovery['menuFiles'] : [];
        $controllerFiles = isset($discovery['controllerFiles']) && is_array($discovery['controllerFiles']) ? $discovery['controllerFiles'] : [];

        $route = is_array($routes[0] ?? null) ? $routes[0] : [];
        $menu = is_array($menuItems[0] ?? null) ? $menuItems[0] : [];

        $checks = [
            'single_route' => count($routes) === 1,
            'single_menu_item' => count($menuItems) === 1,
            'route_name_ok' => ($route['name'] ?? '') === 'admin.page_momentum.index',
            'route_method_get' => ($route['method'] ?? '') === 'GET',
            'route_path_ok' => ($route['path'] ?? '') === '/admin/page-momentum',
            'route_permission_page_manage' => ($route['permission'] ?? '') === 'page.manage',
            'menu_route_matches' => ($menu['route'] ?? '') === 'admin.page_momentum.index',
            'menu_permission_page_manage' => ($menu['permission'] ?? '') === 'page.manage',
            'adapter_live_mutation_false' => ($adapterExport['liveMutation'] ?? true) === false,
        ];

        return [
            'readyForSourcePatch' => !in_array(false, $checks, true),
            'checks' => $checks,
            'routes' => $routes,
            'menuItems' => $menuItems,
            'routeCount' => count($routes),
            'menuCount' => count($menuItems),
            'routeFilesDiscovered' => array_values($routeFiles),
            'menuFilesDiscovered' => array_values($menuFiles),
            'controllerFilesDiscovered' => array_values($controllerFiles),
            'recommendedPatch' => [
                'inspect the discovered admin route/menu aggregator files before editing',
                'load PageMomentumAdminSourceHookAdapter from the page module',
                'append exactly one GET /admin/page-momentum route when not already registered',
                'append exactly one Page momentum menu item when not already registered',
                'guard route and menu with page.manage',
                'smoke /admin and /admin/page-momentum, run Pest, and inspect logs',
            ],
            'rollback' => [
                'remove the source-level route/menu hook added by the next phase',
                'remove app/zoosper-page/config/admin_page_momentum_source_hook_adapter.php only if needed',
                'keep metadata active unless runtime smoke fails',
                'if smoke fails, set page_momentum, page_momentum_routes, and page_momentum_menu enabled flags to false',
                'rerun Pest and inspect nginx/application logs',
            ],
            'liveMutation' => false,
        ];
    }
}
