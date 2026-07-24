<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Builds a deterministic integration plan for Page Momentum admin route/menu wiring.
 */
final class PageMomentumAggregatorIntegrationPlan
{
    /**
     * @param array<string, mixed> $routeConfig
     * @param array<string, mixed> $menuConfig
     * @param array<string, mixed> $discovery
     * @return array<string, mixed>
     */
    public function build(array $routeConfig, array $menuConfig, array $discovery): array
    {
        $routeRoot = $routeConfig['page_momentum_routes'] ?? [];
        $menuRoot = $menuConfig['page_momentum_menu'] ?? [];
        $routes = is_array($routeRoot) && isset($routeRoot['routes']) && is_array($routeRoot['routes']) ? array_values($routeRoot['routes']) : [];
        $items = is_array($menuRoot) && isset($menuRoot['items']) && is_array($menuRoot['items']) ? array_values($menuRoot['items']) : [];

        $routeFiles = $discovery['routeFiles'] ?? [];
        $menuFiles = $discovery['menuFiles'] ?? [];
        $controllerFiles = $discovery['controllerFiles'] ?? [];

        $hasRouteConvention = is_array($routeFiles) && count($routeFiles) > 0;
        $hasMenuConvention = is_array($menuFiles) && count($menuFiles) > 0;
        $hasControllerConvention = is_array($controllerFiles) && count($controllerFiles) > 0;

        return [
            'routeMetadataEnabled' => is_array($routeRoot) && ($routeRoot['enabled'] ?? false) === true,
            'menuMetadataEnabled' => is_array($menuRoot) && ($menuRoot['enabled'] ?? false) === true,
            'routeCount' => count($routes),
            'menuCount' => count($items),
            'hasRouteConvention' => $hasRouteConvention,
            'hasMenuConvention' => $hasMenuConvention,
            'hasControllerConvention' => $hasControllerConvention,
            'readyForPatchDraft' => count($routes) === 1 && count($items) === 1 && $hasMenuConvention && ($hasRouteConvention || $hasControllerConvention),
            'recommendedNextAction' => ($hasMenuConvention && ($hasRouteConvention || $hasControllerConvention))
                ? 'draft smallest router/menu aggregator patch'
                : 'inspect runtime router/menu aggregation manually before patching',
            'liveMutation' => false,
        ];
    }
}
