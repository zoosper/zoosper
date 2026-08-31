<?php

declare(strict_types=1);

it('wires canonical admin path expansion atomically into routes and menus', function (): void {
    $root = dirname(__DIR__, 5);
    $routes = (string) file_get_contents($root . '/app/zoosper-core/src/Routing/ModuleRouteLoader.php');
    $factory = (string) file_get_contents($root . '/app/zoosper-core/src/Bootstrap/ApplicationFactory.php');
    $coreServices = (string) file_get_contents($root . '/app/zoosper-core/config/services.php');
    $menu = (string) file_get_contents($root . '/app/zoosper-admin/src/Navigation/AdminMenuLoader.php');
    $adminServices = (string) file_get_contents($root . '/app/zoosper-admin/config/services.php');

    expect($routes)->toContain("\$configFile === 'admin_routes.php'")
        ->toContain('$this->adminPaths->routes($config)')
        ->toContain('private ?AdminPathCollectionTransformer $adminPaths = null')
        ->and($factory)->toContain('$services->get(AdminPathCollectionTransformer::class)')
        ->and($coreServices)->toContain('AdminUrlGenerator::class =>')
        ->toContain('AdminPathCollectionTransformer::class =>')
        ->and($menu)->toContain('$this->adminPaths?->menu($config) ?? $config')
        ->toContain('private ?AdminPathCollectionTransformer $adminPaths = null')
        ->and($adminServices)->toContain('$services->get(AdminPathCollectionTransformer::class)');
});

it('leaves API route declarations outside admin path expansion', function (): void {
    $root = dirname(__DIR__, 5);
    $routes = (string) file_get_contents($root . '/app/zoosper-core/src/Routing/ModuleRouteLoader.php');

    expect($routes)->toContain("\$configFile === 'admin_routes.php'")
        ->toContain("registerRoutesFromConfig(\$router, 'api_routes.php', [])");
});










