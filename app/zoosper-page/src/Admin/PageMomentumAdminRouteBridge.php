<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Converts the isolated Page Momentum candidate into admin route definitions.
 *
 * This bridge performs no registration. It only returns a normalised route list
 * for a future admin route aggregator hook.
 */
final class PageMomentumAdminRouteBridge
{
    /**
     * @param array<string, mixed> $candidate
     * @return list<array<string, mixed>>
     */
    public function routes(array $candidate): array
    {
        $root = $candidate['page_momentum_admin_integration'] ?? [];
        if (!is_array($root) || ($root['enabled'] ?? false) !== true) {
            return [];
        }

        $routes = isset($root['routes']) && is_array($root['routes']) ? $root['routes'] : [];

        return array_values(array_filter(
            $routes,
            static fn (mixed $route): bool => is_array($route)
                && ($route['name'] ?? '') === 'admin.page_momentum.index'
                && ($route['method'] ?? '') === 'GET'
                && ($route['path'] ?? '') === '/admin/page-momentum'
                && isset($route['controller'], $route['action'], $route['permission'])
        ));
    }
}
