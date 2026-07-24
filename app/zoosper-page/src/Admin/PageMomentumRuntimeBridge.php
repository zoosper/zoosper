<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Combined bridge for future page momentum admin route/menu wiring.
 */
final readonly class PageMomentumRuntimeBridge
{
    public function __construct(
        private PageMomentumRouteDefinitionProvider $routes = new PageMomentumRouteDefinitionProvider(),
        private PageMomentumMenuDefinitionProvider $menu = new PageMomentumMenuDefinitionProvider(),
    ) {
    }

    /**
     * @param array<string, mixed> $routeConfig
     * @param array<string, mixed> $menuConfig
     * @return array{routes: list<array<string, mixed>>, menuItems: list<array<string, mixed>>, routeCount: int, menuCount: int}
     */
    public function definitions(array $routeConfig, array $menuConfig): array
    {
        $routes = $this->routes->routes($routeConfig);
        $menuItems = $this->menu->items($menuConfig);

        return [
            'routes' => $routes,
            'menuItems' => $menuItems,
            'routeCount' => count($routes),
            'menuCount' => count($menuItems),
        ];
    }
}
