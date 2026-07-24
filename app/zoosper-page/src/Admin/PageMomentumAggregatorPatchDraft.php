<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Builds a deterministic patch-draft payload for the Page Momentum admin panel.
 *
 * This class does not write to router or menu aggregators. It only prepares a
 * readable plan for the next live integration phase.
 */
final class PageMomentumAggregatorPatchDraft
{
    /**
     * @param array<string, mixed> $integrationPlan
     * @param array<string, mixed> $routeConfig
     * @param array<string, mixed> $menuConfig
     * @return array<string, mixed>
     */
    public function draft(array $integrationPlan, array $routeConfig, array $menuConfig): array
    {
        $routesRoot = $routeConfig['page_momentum_routes'] ?? [];
        $menuRoot = $menuConfig['page_momentum_menu'] ?? [];
        $routes = is_array($routesRoot) && isset($routesRoot['routes']) && is_array($routesRoot['routes']) ? array_values($routesRoot['routes']) : [];
        $items = is_array($menuRoot) && isset($menuRoot['items']) && is_array($menuRoot['items']) ? array_values($menuRoot['items']) : [];
        $route = is_array($routes[0] ?? null) ? $routes[0] : [];
        $item = is_array($items[0] ?? null) ? $items[0] : [];

        $readyForPatch = ($integrationPlan['readyForPatchDraft'] ?? false) === true
            && ($routesRoot['enabled'] ?? false) === true
            && ($menuRoot['enabled'] ?? false) === true
            && ($route['name'] ?? '') === 'admin.page_momentum.index'
            && ($item['route'] ?? '') === 'admin.page_momentum.index';

        return [
            'readyForPatchDraft' => $readyForPatch,
            'routeName' => $route['name'] ?? '',
            'routeMethod' => $route['method'] ?? '',
            'routePath' => $route['path'] ?? '',
            'routeController' => isset($route['controller']) && is_string($route['controller']) ? $route['controller'] : '',
            'routeAction' => $route['action'] ?? '',
            'routePermission' => $route['permission'] ?? '',
            'menuLabel' => $item['label'] ?? '',
            'menuRoute' => $item['route'] ?? '',
            'menuPermission' => $item['permission'] ?? '',
            'recommendedPatch' => [
                'register PageMomentumAdminController::index as GET /admin/page-momentum',
                'register Page momentum menu item using admin.page_momentum.index',
                'guard route and menu with page.manage permission',
                'run smoke check and rollback if admin bootstrap fails',
            ],
            'rollback' => [
                'remove route/menu aggregator additions from the next patch',
                'set page_momentum.enabled to false if metadata activation must be reverted',
                'set page_momentum_routes.enabled to false if route metadata must be reverted',
                'set page_momentum_menu.enabled to false if menu metadata must be reverted',
                'rerun full Pest suite and check nginx/application logs',
            ],
            'liveMutation' => false,
        ];
    }
}
