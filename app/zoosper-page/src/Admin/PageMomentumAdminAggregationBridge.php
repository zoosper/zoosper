<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Combined read-only bridge for the Page Momentum admin route/menu candidate.
 */
final readonly class PageMomentumAdminAggregationBridge
{
    public function __construct(
        private PageMomentumAdminRouteBridge $routeBridge = new PageMomentumAdminRouteBridge(),
        private PageMomentumAdminMenuBridge $menuBridge = new PageMomentumAdminMenuBridge(),
    ) {
    }

    /**
     * @param array<string, mixed> $candidate
     * @return array{routes: list<array<string, mixed>>, menuItems: list<array<string, mixed>>, routeCount: int, menuCount: int, liveMutation: bool}
     */
    public function export(array $candidate): array
    {
        $routes = $this->routeBridge->routes($candidate);
        $items = $this->menuBridge->items($candidate);

        return [
            'routes' => $routes,
            'menuItems' => $items,
            'routeCount' => count($routes),
            'menuCount' => count($items),
            'liveMutation' => false,
        ];
    }
}
