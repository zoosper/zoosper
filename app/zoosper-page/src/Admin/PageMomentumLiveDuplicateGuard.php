<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Read-only duplicate guard for the live Page Momentum route/menu config.
 */
final class PageMomentumLiveDuplicateGuard
{
    /**
     * @param array<int|string, mixed> $routeConfig
     * @param array<int|string, mixed> $menuConfig
     * @return array{routeMatches: int, menuMatches: int, routeOk: bool, menuOk: bool, ok: bool}
     */
    public function inspect(array $routeConfig, array $menuConfig): array
    {
        $routes = $this->normaliseList($routeConfig, 'routes');
        $items = $this->normaliseList($menuConfig, 'items');

        $routeMatches = 0;
        foreach ($routes as $route) {
            if (($route['name'] ?? '') === 'admin.page_momentum.index'
                || ($route['path'] ?? '') === '/admin/page-momentum') {
                $routeMatches++;
            }
        }

        $menuMatches = 0;
        foreach ($items as $item) {
            if (($item['route'] ?? '') === 'admin.page_momentum.index') {
                $menuMatches++;
            }
        }

        return [
            'routeMatches' => $routeMatches,
            'menuMatches' => $menuMatches,
            'routeOk' => $routeMatches === 1,
            'menuOk' => $menuMatches === 1,
            'ok' => $routeMatches === 1 && $menuMatches === 1,
        ];
    }

    /**
     * @param array<int|string, mixed> $config
     * @return list<array<string, mixed>>
     */
    private function normaliseList(array $config, string $key): array
    {
        $items = array_is_list($config) ? $config : ($config[$key] ?? []);
        if (!is_array($items)) {
            return [];
        }

        return array_values(array_filter($items, 'is_array'));
    }
}
