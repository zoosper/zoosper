<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;

/**
 * Validates that page momentum metadata is internally consistent after activation.
 */
final class PageMomentumActivationGuard
{
    /**
     * @param array<string, mixed> $momentumConfig
     * @param array<string, mixed> $routeConfig
     * @param array<string, mixed> $menuConfig
     * @return array{ready: bool, checks: array<string, bool>, rollback: list<string>}
     */
    public function inspect(array $momentumConfig, array $routeConfig, array $menuConfig): array
    {
        $momentumRoot = $momentumConfig['page_momentum'] ?? [];
        $routeRoot = $routeConfig['page_momentum_routes'] ?? [];
        $menuRoot = $menuConfig['page_momentum_menu'] ?? [];

        $routes = is_array($routeRoot) && isset($routeRoot['routes']) && is_array($routeRoot['routes']) ? $routeRoot['routes'] : [];
        $items = is_array($menuRoot) && isset($menuRoot['items']) && is_array($menuRoot['items']) ? $menuRoot['items'] : [];
        $route = is_array($routes[0] ?? null) ? $routes[0] : [];
        $item = is_array($items[0] ?? null) ? $items[0] : [];

        $checks = [
            'momentum_enabled' => is_array($momentumRoot) && ($momentumRoot['enabled'] ?? false) === true,
            'route_metadata_enabled' => is_array($routeRoot) && ($routeRoot['enabled'] ?? false) === true,
            'menu_metadata_enabled' => is_array($menuRoot) && ($menuRoot['enabled'] ?? false) === true,
            'route_name_ok' => ($route['name'] ?? '') === 'admin.page_momentum.index',
            'route_method_get' => ($route['method'] ?? '') === 'GET',
            'route_path_ok' => ($route['path'] ?? '') === '/admin/page-momentum',
            'route_controller_ok' => ($route['controller'] ?? '') === PageMomentumAdminController::class,
            'route_action_ok' => ($route['action'] ?? '') === 'index',
            'route_permission_ok' => ($route['permission'] ?? '') === 'page.manage',
            'menu_route_ok' => ($item['route'] ?? '') === 'admin.page_momentum.index',
            'menu_permission_ok' => ($item['permission'] ?? '') === 'page.manage',
            'controller_autoloadable' => class_exists(PageMomentumAdminController::class),
        ];

        return [
            'ready' => !in_array(false, $checks, true),
            'checks' => $checks,
            'rollback' => [
                'set page_momentum.enabled to false',
                'set page_momentum_routes.enabled to false',
                'set page_momentum_menu.enabled to false',
                'rerun the full Pest suite',
                'check nginx and var/log/exception.log after deployment',
            ],
        ];
    }
}
