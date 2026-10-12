<?php

declare(strict_types=1);

use Zoosper\Core\Routing\ModuleRouteDefinition;

it('classifies every Admin route as explicitly public or permission protected', function (): void {
    $root = dirname(__DIR__, 5);
    $files = [];
    foreach ([$root . '/app', $root . '/packages'] as $base) {
        $iterator = new \RegexIterator(
            new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base)),
            '#/config/admin_routes\.php$#',
        );
        foreach ($iterator as $file) {
            $files[] = $file->getPathname();
        }
    }
    sort($files);
    expect($files)->not->toBe([]);

    foreach ($files as $file) {
        $routes = require $file;
        expect($routes)->toBeArray();
        foreach ($routes as $route) {
            expect($route)->toBeArray();
            $public = ($route['public'] ?? false) === true;
            $permissions = ModuleRouteDefinition::normalisePermissions($route['permission'] ?? null);
            expect($public xor $permissions !== [])
                ->toBeTrue(sprintf('Admin route must be exactly public or permission protected: %s %s (%s)', $route['method'] ?? '', $route['path'] ?? '', $file));
        }
    }
});
