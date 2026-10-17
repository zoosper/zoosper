<?php

declare(strict_types=1);

use Zoosper\Menu\Api\MenuApiController;

it('exposes bearer-protected stateless Menu reads with Site isolation', function (): void {
    $root = dirname(__DIR__, 5);
    $routes = require $root . '/app/zoosper-menu/config/api_routes.php';
    $protected = array_values(array_filter(
        $routes,
        static fn (array $route): bool => ($route['controller'] ?? null) === MenuApiController::class
            && in_array($route['action'] ?? '', ['index', 'show', 'tree'], true),
    ));
    expect(array_column($protected, 'path'))->toEqualCanonicalizing([
        '/api/v1/menus',
        '/api/v1/menus/{id:\d+}',
        '/api/v1/menus/{id:\d+}/tree',
    ]);
    foreach ($protected as $route) {
        expect($route['auth'])->toBe('bearer')
            ->and($route['public'])->toBeFalse()
            ->and($route['stateless'])->toBeTrue()
            ->and($route['scope'])->toBe('menus:read');
    }
    $controller = (string) file_get_contents($root . '/app/zoosper-menu/src/Api/MenuApiController.php');
    expect($controller)->toContain("allows('menus:read')")
        ->toContain("can('menu.manage')")
        ->toContain('$request->siteContext()?->siteId')
        ->toContain('MenuProviderInterface')
        ->not->toContain('SessionGuard');
});
