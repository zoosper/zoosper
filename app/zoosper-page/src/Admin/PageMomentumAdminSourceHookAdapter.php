<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Source-level adapter for exposing Page Momentum admin route/menu definitions.
 *
 * This adapter is intentionally passive. It reads the isolated hook candidate and
 * returns route/menu arrays in a stable shape for a future admin aggregation
 * source patch. It does not mutate router/menu runtime state.
 */
final class PageMomentumAdminSourceHookAdapter
{
    /**
     * @param array<string, mixed> $hookCandidate
     * @return array{routes: list<array<string, mixed>>, menuItems: list<array<string, mixed>>, routeCount: int, menuCount: int, liveMutation: bool, rollback: list<string>}
     */
    public function expose(array $hookCandidate): array
    {
        $root = $hookCandidate['page_momentum_admin_hook'] ?? [];
        if (!is_array($root) || ($root['enabled'] ?? false) !== true) {
            return [
                'routes' => [],
                'menuItems' => [],
                'routeCount' => 0,
                'menuCount' => 0,
                'liveMutation' => false,
                'rollback' => [],
            ];
        }

        $routes = isset($root['routes']) && is_array($root['routes'])
            ? array_values(array_filter($root['routes'], 'is_array'))
            : [];
        $menuItems = isset($root['menu_items']) && is_array($root['menu_items'])
            ? array_values(array_filter($root['menu_items'], 'is_array'))
            : [];
        $rollback = isset($root['rollback']) && is_array($root['rollback'])
            ? array_values(array_filter($root['rollback'], 'is_string'))
            : [];

        $routes = array_values(array_filter(
            $routes,
            static fn (array $route): bool => ($route['name'] ?? '') === 'admin.page_momentum.index'
                && ($route['method'] ?? '') === 'GET'
                && ($route['path'] ?? '') === '/admin/page-momentum'
                && ($route['permission'] ?? '') === 'page.manage'
        ));

        $menuItems = array_values(array_filter(
            $menuItems,
            static fn (array $item): bool => ($item['route'] ?? '') === 'admin.page_momentum.index'
                && ($item['permission'] ?? '') === 'page.manage'
        ));

        return [
            'routes' => $routes,
            'menuItems' => $menuItems,
            'routeCount' => count($routes),
            'menuCount' => count($menuItems),
            'liveMutation' => false,
            'rollback' => $rollback,
        ];
    }
}
