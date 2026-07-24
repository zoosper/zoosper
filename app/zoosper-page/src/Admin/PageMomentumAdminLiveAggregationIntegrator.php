<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Merges the passive Page Momentum route/menu hook output into conventional
 * config arrays for the page module.
 *
 * The class is intentionally framework-agnostic. It does not touch the file
 * system and can be tested fully in memory.
 */
final class PageMomentumAdminLiveAggregationIntegrator
{
    /**
     * @param array<string, mixed> $routeConfig
     * @param list<array<string, mixed>> $routes
     * @return array<string, mixed>|list<array<string, mixed>>
     */
    public function mergeRoutes(array $routeConfig, array $routes): array
    {
        $route = $this->firstValidRoute($routes);
        if ($route === null) {
            return $routeConfig;
        }

        if ($this->isListOfArrays($routeConfig)) {
            return $this->appendUniqueByRouteIdentity($routeConfig, $route);
        }

        if (!isset($routeConfig['routes']) || !is_array($routeConfig['routes'])) {
            $routeConfig['routes'] = [];
        }

        $routeConfig['routes'] = $this->appendUniqueByRouteIdentity($routeConfig['routes'], $route);

        return $routeConfig;
    }

    /**
     * @param array<string, mixed> $menuConfig
     * @param list<array<string, mixed>> $items
     * @return array<string, mixed>|list<array<string, mixed>>
     */
    public function mergeMenu(array $menuConfig, array $items): array
    {
        $item = $this->firstValidMenuItem($items);
        if ($item === null) {
            return $menuConfig;
        }

        if ($this->isListOfArrays($menuConfig)) {
            return $this->appendUniqueByMenuIdentity($menuConfig, $item);
        }

        if (!isset($menuConfig['items']) || !is_array($menuConfig['items'])) {
            $menuConfig['items'] = [];
        }

        $menuConfig['items'] = $this->appendUniqueByMenuIdentity($menuConfig['items'], $item);

        return $menuConfig;
    }

    /**
     * @param list<array<string, mixed>> $routes
     * @return array<string, mixed>|null
     */
    private function firstValidRoute(array $routes): ?array
    {
        foreach ($routes as $route) {
            if (($route['name'] ?? '') === 'admin.page_momentum.index'
                && ($route['method'] ?? '') === 'GET'
                && ($route['path'] ?? '') === '/admin/page-momentum'
                && ($route['permission'] ?? '') === 'page.manage') {
                return $route;
            }
        }

        return null;
    }

    /**
     * @param list<array<string, mixed>> $items
     * @return array<string, mixed>|null
     */
    private function firstValidMenuItem(array $items): ?array
    {
        foreach ($items as $item) {
            if (($item['route'] ?? '') === 'admin.page_momentum.index'
                && ($item['permission'] ?? '') === 'page.manage') {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param array<int|string, mixed> $items
     */
    private function isListOfArrays(array $items): bool
    {
        if ($items === []) {
            return true;
        }

        return array_is_list($items) && array_reduce(
            $items,
            static fn (bool $carry, mixed $item): bool => $carry && is_array($item),
            true,
        );
    }

    /**
     * @param array<int|string, mixed> $routes
     * @param array<string, mixed> $route
     * @return array<int|string, mixed>
     */
    private function appendUniqueByRouteIdentity(array $routes, array $route): array
    {
        foreach ($routes as $existing) {
            if (!is_array($existing)) {
                continue;
            }
            if (($existing['name'] ?? null) === ($route['name'] ?? null)
                || (($existing['method'] ?? null) === ($route['method'] ?? null) && ($existing['path'] ?? null) === ($route['path'] ?? null))) {
                return $routes;
            }
        }

        $routes[] = $route;

        return $routes;
    }

    /**
     * @param array<int|string, mixed> $items
     * @param array<string, mixed> $item
     * @return array<int|string, mixed>
     */
    private function appendUniqueByMenuIdentity(array $items, array $item): array
    {
        foreach ($items as $existing) {
            if (!is_array($existing)) {
                continue;
            }
            if (($existing['route'] ?? null) === ($item['route'] ?? null)) {
                return $items;
            }
        }

        $items[] = $item;

        return $items;
    }
}
