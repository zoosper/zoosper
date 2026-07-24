<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Passive route/menu hook for the Page Momentum admin panel.
 *
 * The real admin aggregation pipeline can call this hook to retrieve one route
 * and one matching menu item. This class does not mutate router or menu runtime
 * state by itself.
 */
final class PageMomentumAdminRouteMenuHook
{
    public function __construct(
        private readonly PageMomentumAdminRuntimeAggregationProvider $provider = new PageMomentumAdminRuntimeAggregationProvider(),
    ) {
    }

    /**
     * @param array<string, mixed> $runtimeConfig
     * @param array<string, mixed> $hookCandidate
     * @return list<array<string, mixed>>
     */
    public function routes(array $runtimeConfig, array $hookCandidate): array
    {
        $payload = $this->provider->provide($runtimeConfig, $hookCandidate);

        if (($payload['enabled'] ?? false) !== true || ($payload['liveMutation'] ?? true) !== false) {
            return [];
        }

        return $payload['routes'];
    }

    /**
     * @param array<string, mixed> $runtimeConfig
     * @param array<string, mixed> $hookCandidate
     * @return list<array<string, mixed>>
     */
    public function menuItems(array $runtimeConfig, array $hookCandidate): array
    {
        $payload = $this->provider->provide($runtimeConfig, $hookCandidate);

        if (($payload['enabled'] ?? false) !== true || ($payload['liveMutation'] ?? true) !== false) {
            return [];
        }

        return $payload['menuItems'];
    }

    /**
     * @param array<string, mixed> $runtimeConfig
     * @param array<string, mixed> $hookCandidate
     * @return array{routes: list<array<string, mixed>>, menuItems: list<array<string, mixed>>, routeCount: int, menuCount: int, liveMutation: bool}
     */
    public function export(array $runtimeConfig, array $hookCandidate): array
    {
        $routes = $this->routes($runtimeConfig, $hookCandidate);
        $menuItems = $this->menuItems($runtimeConfig, $hookCandidate);

        return [
            'routes' => $routes,
            'menuItems' => $menuItems,
            'routeCount' => count($routes),
            'menuCount' => count($menuItems),
            'liveMutation' => false,
        ];
    }
}
