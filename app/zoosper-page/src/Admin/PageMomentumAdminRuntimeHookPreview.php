<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Read-only preview for the eventual admin route/menu aggregation runtime hook.
 *
 * This class validates the exact payload that should be passed to the real admin
 * aggregation pipeline. It does not register routes or menu entries.
 */
final class PageMomentumAdminRuntimeHookPreview
{
    /**
     * @param array<string, mixed> $runtimePayload
     * @return array<string, mixed>
     */
    public function preview(array $runtimePayload): array
    {
        $routes = isset($runtimePayload['routes']) && is_array($runtimePayload['routes'])
            ? array_values(array_filter($runtimePayload['routes'], 'is_array'))
            : [];
        $menuItems = isset($runtimePayload['menuItems']) && is_array($runtimePayload['menuItems'])
            ? array_values(array_filter($runtimePayload['menuItems'], 'is_array'))
            : [];
        $rollback = isset($runtimePayload['rollback']) && is_array($runtimePayload['rollback'])
            ? array_values(array_filter($runtimePayload['rollback'], 'is_string'))
            : [];

        $route = is_array($routes[0] ?? null) ? $routes[0] : [];
        $menu = is_array($menuItems[0] ?? null) ? $menuItems[0] : [];

        $checks = [
            'runtime_payload_enabled' => ($runtimePayload['enabled'] ?? false) === true,
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
            'rollback_present' => count($rollback) > 0,
            'live_mutation_false' => ($runtimePayload['liveMutation'] ?? true) === false,
        ];

        return [
            'readyForRuntimeSourceHook' => !in_array(false, $checks, true),
            'checks' => $checks,
            'routes' => $routes,
            'menuItems' => $menuItems,
            'routeCount' => count($routes),
            'menuCount' => count($menuItems),
            'rollback' => $rollback,
            'liveMutation' => false,
        ];
    }
}
