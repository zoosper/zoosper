<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
use Zoosper\Page\Admin\Controller\PageMomentumAdminHttpController;

/**
 * Provides real read-only facts for the Page Admin dashboard.
 */
final class PageAdminDashboardFactProvider
{
    public function __construct(
        private readonly ?string $root = null,
    ) {
    }

    /**
     * @return list<array{label: string, status: string, detail: string}>
     */
    public function facts(): array
    {
        $root = $this->root ?? dirname(__DIR__, 4);
        $routeConfig = $this->firstArrayConfig([
            $root . '/app/zoosper-page/config/admin_routes.php',
            $root . '/app/zoosper-page/config/routes.php',
        ]);
        $menuConfig = $this->firstArrayConfig([
            $root . '/app/zoosper-page/config/admin_menu.php',
        ]);

        $routeFound = $this->hasRoute($routeConfig, 'admin.page_momentum.index', '/admin/page-momentum');
        $menuFound = $this->hasMenuItem($menuConfig, 'admin.page_momentum.index');
        $rendererAvailable = class_exists(PageMomentumAdminController::class);
        $httpControllerAvailable = class_exists(PageMomentumAdminHttpController::class);
        $liveHttpController = $this->hasController($routeConfig, PageMomentumAdminHttpController::class);

        return [
            [
                'label' => 'Live route fact',
                'status' => $routeFound ? 'ready' : 'planned',
                'detail' => $routeFound
                    ? 'The live route config contains admin.page_momentum.index for /admin/page-momentum.'
                    : 'The live route config does not yet expose admin.page_momentum.index for /admin/page-momentum.',
            ],
            [
                'label' => 'Live menu fact',
                'status' => $menuFound ? 'ready' : 'planned',
                'detail' => $menuFound
                    ? 'The admin menu config contains a menu item pointing to admin.page_momentum.index.'
                    : 'The admin menu config does not yet contain a menu item for admin.page_momentum.index.',
            ],
            [
                'label' => 'Renderer controller fact',
                'status' => $rendererAvailable ? 'ready' : 'planned',
                'detail' => $rendererAvailable
                    ? 'PageMomentumAdminController is autoloadable as the string-rendering dashboard controller.'
                    : 'PageMomentumAdminController is not currently autoloadable.',
            ],
            [
                'label' => 'HTTP controller fact',
                'status' => ($httpControllerAvailable && $liveHttpController) ? 'ready' : 'track',
                'detail' => ($httpControllerAvailable && $liveHttpController)
                    ? 'The live route config points to PageMomentumAdminHttpController so the router can receive a Response object.'
                    : 'The HTTP controller or live route binding still needs checking.',
            ],
        ];
    }

    /**
     * @param list<string> $paths
     * @return array<int|string, mixed>
     */
    private function firstArrayConfig(array $paths): array
    {
        foreach ($paths as $path) {
            if (!is_file($path)) {
                continue;
            }
            $config = require $path;
            if (is_array($config)) {
                return $config;
            }
        }

        return [];
    }

    /**
     * @param array<int|string, mixed> $config
     */
    private function hasRoute(array $config, string $name, string $path): bool
    {
        foreach ($this->normaliseList($config, 'routes') as $route) {
            if (($route['name'] ?? '') === $name || ($route['path'] ?? '') === $path) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int|string, mixed> $config
     */
    private function hasMenuItem(array $config, string $routeName): bool
    {
        foreach ($this->normaliseList($config, 'items') as $item) {
            if (($item['route'] ?? '') === $routeName) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int|string, mixed> $config
     */
    private function hasController(array $config, string $controller): bool
    {
        return $this->containsController($config, $controller);
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

    /**
     * @param mixed $value
     */
    private function containsController(mixed $value, string $controller): bool
    {
        if (!is_array($value)) {
            return false;
        }

        foreach ($value as $key => $item) {
            if ($key === 'controller' && $item === $controller) {
                return true;
            }
            if ($this->containsController($item, $controller)) {
                return true;
            }
        }

        return false;
    }
}
