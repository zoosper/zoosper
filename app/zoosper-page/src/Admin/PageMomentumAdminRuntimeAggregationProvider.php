<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Runtime-facing provider for Page Momentum admin route/menu definitions.
 *
 * This provider is intentionally passive. It prepares a route/menu payload for
 * the real admin aggregation pipeline but does not register anything by itself.
 */
final class PageMomentumAdminRuntimeAggregationProvider
{
    public function __construct(
        private readonly PageMomentumAdminSourceHookAdapter $adapter = new PageMomentumAdminSourceHookAdapter(),
    ) {
    }

    /**
     * @param array<string, mixed> $adapterConfig
     * @param array<string, mixed> $hookCandidate
     * @return array{enabled: bool, routes: list<array<string, mixed>>, menuItems: list<array<string, mixed>>, routeCount: int, menuCount: int, liveMutation: bool, rollback: list<string>}
     */
    public function provide(array $adapterConfig, array $hookCandidate): array
    {
        $root = $adapterConfig['page_momentum_runtime_aggregation_candidate']
            ?? $adapterConfig['page_momentum_source_hook_adapter']
            ?? [];

        if (!is_array($root) || ($root['enabled'] ?? false) !== true) {
            return [
                'enabled' => false,
                'routes' => [],
                'menuItems' => [],
                'routeCount' => 0,
                'menuCount' => 0,
                'liveMutation' => false,
                'rollback' => [],
            ];
        }

        $exposed = $this->adapter->expose($hookCandidate);
        $routes = $exposed['routes'];
        $menuItems = $exposed['menuItems'];

        $enabled = count($routes) === 1
            && count($menuItems) === 1
            && ($routes[0]['name'] ?? '') === 'admin.page_momentum.index'
            && ($menuItems[0]['route'] ?? '') === 'admin.page_momentum.index'
            && ($exposed['liveMutation'] ?? true) === false;

        return [
            'enabled' => $enabled,
            'routes' => $routes,
            'menuItems' => $menuItems,
            'routeCount' => count($routes),
            'menuCount' => count($menuItems),
            'liveMutation' => false,
            'rollback' => $exposed['rollback'],
        ];
    }
}
