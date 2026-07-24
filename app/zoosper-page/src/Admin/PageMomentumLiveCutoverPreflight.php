<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;

/**
 * Verifies that page momentum route/menu metadata is internally safe before a
 * future live admin cutover.
 *
 * This service does not register routes or menu entries.
 */
final class PageMomentumLiveCutoverPreflight
{
    /**
     * @param array<string, mixed> $routeConfig
     * @param array<string, mixed> $menuConfig
     * @return array<string, mixed>
     */
    public function inspect(array $routeConfig, array $menuConfig): array
    {
        $routeRoot = $routeConfig['page_momentum_routes'] ?? [];
        $menuRoot = $menuConfig['page_momentum_menu'] ?? [];
        $routes = is_array($routeRoot) && isset($routeRoot['routes']) && is_array($routeRoot['routes']) ? $routeRoot['routes'] : [];
        $menuItems = is_array($menuRoot) && isset($menuRoot['items']) && is_array($menuRoot['items']) ? $menuRoot['items'] : [];
        $route = is_array($routes[0] ?? null) ? $routes[0] : [];
        $menu = is_array($menuItems[0] ?? null) ? $menuItems[0] : [];

        $controllerClass = PageMomentumAdminController::class;
        $routeName = (string) ($route['name'] ?? '');
        $permission = (string) ($route['permission'] ?? '');
        $menuRoute = (string) ($menu['route'] ?? '');
        $menuPermission = (string) ($menu['permission'] ?? '');

        $checks = [
            'route_metadata_disabled' => ($routeRoot['enabled'] ?? true) === false,
            'menu_metadata_disabled' => ($menuRoot['enabled'] ?? true) === false,
            'route_name_present' => $routeName !== '',
            'route_method_get' => ($route['method'] ?? '') === 'GET',
            'route_path_present' => isset($route['path']) && is_string($route['path']) && $route['path'] !== '',
            'route_controller_matches' => ($route['controller'] ?? '') === $controllerClass,
            'route_action_index' => ($route['action'] ?? '') === 'index',
            'route_permission_page_manage' => $permission === 'page.manage',
            'menu_route_matches_route_name' => $menuRoute !== '' && $menuRoute === $routeName,
            'menu_permission_matches_route_permission' => $menuPermission !== '' && $menuPermission === $permission,
            'controller_autoloadable' => class_exists($controllerClass),
        ];

        return [
            'readyForManualCutover' => !in_array(false, $checks, true),
            'checks' => $checks,
            'route' => $route,
            'menu' => $menu,
            'liveMutation' => false,
        ];
    }
}
