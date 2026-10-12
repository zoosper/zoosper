<?php

declare(strict_types=1);

use Zoosper\Core\Routing\ModuleRouteDefinition;
use Zoosper\Errors\ZoosperException;

it('accepts an intentionally public Admin route', function (): void {
    $route = new ModuleRouteDefinition('GET', '/admin/login', 'Controller', 'show', public: true);

    expect(fn (): mixed => $route->assertExplicitAdminAccess())->not->toThrow(ZoosperException::class);
});

it('accepts a permission-protected Admin route', function (): void {
    $route = new ModuleRouteDefinition('GET', '/admin', 'Controller', 'index', ['admin.access']);

    expect(fn (): mixed => $route->assertExplicitAdminAccess())->not->toThrow(ZoosperException::class);
});

it('rejects an Admin route with no explicit access classification', function (): void {
    $route = new ModuleRouteDefinition('GET', '/admin/implicit', 'Controller', 'index');

    expect(fn (): mixed => $route->assertExplicitAdminAccess())
        ->toThrow(ZoosperException::class, 'Admin route must be exactly public or permission protected: GET /admin/implicit');
});

it('rejects a contradictory public permission-protected Admin route', function (): void {
    $route = new ModuleRouteDefinition('GET', '/admin/contradictory', 'Controller', 'index', ['admin.access'], true);

    expect(fn (): mixed => $route->assertExplicitAdminAccess())
        ->toThrow(ZoosperException::class, 'Admin route must be exactly public or permission protected: GET /admin/contradictory');
});

it('applies the explicit access contract only while loading Admin route manifests', function (): void {
    $source = (string) file_get_contents(dirname(__DIR__, 5) . '/app/zoosper-core/src/Routing/ModuleRouteLoader.php');

    expect($source)
        ->toContain("if (\$configFile === 'admin_routes.php')")
        ->toContain('$definition->assertExplicitAdminAccess();');
});
