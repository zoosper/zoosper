<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Reads page momentum route/menu metadata for future admin wiring.
 *
 * This provider does not register routes or menu items. It simply normalises the
 * disabled-by-default metadata so a later runtime cutover can be tested against
 * one small seam.
 */
final class PageMomentumDefinitionProvider
{
    /**
     * @param array<string, mixed> $routeConfig
     * @param array<string, mixed> $menuConfig
     * @return array{enabled: bool, routes: list<array<string, mixed>>, menuItems: list<array<string, mixed>>}
     */
    public function definitions(array $routeConfig, array $menuConfig): array
    {
        $routeRoot = $routeConfig['page_momentum_routes'] ?? [];
        $menuRoot = $menuConfig['page_momentum_menu'] ?? [];

        $routeEnabled = is_array($routeRoot) && (bool) ($routeRoot['enabled'] ?? false);
        $menuEnabled = is_array($menuRoot) && (bool) ($menuRoot['enabled'] ?? false);

        $routes = is_array($routeRoot) && isset($routeRoot['routes']) && is_array($routeRoot['routes'])
            ? array_values(array_filter($routeRoot['routes'], 'is_array'))
            : [];
        $menuItems = is_array($menuRoot) && isset($menuRoot['items']) && is_array($menuRoot['items'])
            ? array_values(array_filter($menuRoot['items'], 'is_array'))
            : [];

        return [
            'enabled' => $routeEnabled && $menuEnabled,
            'routes' => $routes,
            'menuItems' => $menuItems,
        ];
    }
}
