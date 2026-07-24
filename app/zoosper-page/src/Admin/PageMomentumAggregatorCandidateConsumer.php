<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Reads the isolated Page Momentum aggregator candidate config.
 *
 * This consumer does not register routes or menu items. It presents the isolated
 * candidate in a shape suitable for a later route/menu aggregation pipeline.
 */
final class PageMomentumAggregatorCandidateConsumer
{
    /**
     * @param array<string, mixed> $candidate
     * @return array{enabled: bool, routes: list<array<string, mixed>>, menuItems: list<array<string, mixed>>, routeCount: int, menuCount: int, liveMutation: bool, rollback: list<string>}
     */
    public function consume(array $candidate): array
    {
        $root = $candidate['page_momentum_admin_integration'] ?? [];
        if (!is_array($root)) {
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

        $routes = isset($root['routes']) && is_array($root['routes'])
            ? array_values(array_filter($root['routes'], 'is_array'))
            : [];
        $menuItems = isset($root['menu_items']) && is_array($root['menu_items'])
            ? array_values(array_filter($root['menu_items'], 'is_array'))
            : [];
        $rollback = isset($root['rollback']) && is_array($root['rollback'])
            ? array_values(array_filter($root['rollback'], 'is_string'))
            : [];

        return [
            'enabled' => ($root['enabled'] ?? false) === true,
            'routes' => $routes,
            'menuItems' => $menuItems,
            'routeCount' => count($routes),
            'menuCount' => count($menuItems),
            'liveMutation' => false,
            'rollback' => $rollback,
        ];
    }
}
