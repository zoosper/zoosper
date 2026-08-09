<?php

declare(strict_types=1);

it('uses the authoritative release version for application and Admin presentation', function (): void {
    $root = dirname(__DIR__, 5);
    $version = require $root . '/config/version.php';
    $application = (string) file_get_contents($root . '/config/app.php');
    $layout = (string) file_get_contents($root . '/app/zoosper-admin/src/Layout/AdminLayout.php');
    $environmentExample = (string) file_get_contents($root . '/.env.example');

    expect($version['version'])->toBe('0.2.0-alpha.1-dev')
        ->and($application)
        ->toContain("require __DIR__ . '/version.php'")
        ->toContain("'version' => \$env('CMS_VERSION', \$release['version'])")
        ->not->toContain('0.23.0-dev')
        ->and($layout)
        ->toContain("require dirname(__DIR__, 4) . '/config/version.php'")
        ->not->toContain('0.16.0-dev')
        ->and($environmentExample)
        ->not->toContain('CMS_VERSION=0.8.0-dev');
});
