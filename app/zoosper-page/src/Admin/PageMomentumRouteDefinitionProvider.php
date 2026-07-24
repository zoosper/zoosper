<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Normalises page momentum route metadata for a future admin router integration.
 *
 * This provider does not register routes. It only returns definitions when the
 * supplied metadata is explicitly enabled.
 */
final class PageMomentumRouteDefinitionProvider
{
    /**
     * @param array<string, mixed> $config
     * @return list<array<string, mixed>>
     */
    public function routes(array $config): array
    {
        $root = $config['page_momentum_routes'] ?? [];
        if (!is_array($root) || ($root['enabled'] ?? false) !== true) {
            return [];
        }

        $routes = $root['routes'] ?? [];
        if (!is_array($routes)) {
            return [];
        }

        return array_values(array_filter(
            $routes,
            static fn (mixed $route): bool => is_array($route)
                && isset($route['name'], $route['method'], $route['path'], $route['controller'], $route['action'], $route['permission'])
        ));
    }
}
