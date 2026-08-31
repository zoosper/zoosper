<?php

declare(strict_types=1);

it('guards the alpha critical route and asset inventory', function (): void {
    $root = dirname(__DIR__, 5);
    $routeFiles = array_merge(
        glob($root . '/app/zoosper-*/config/admin_routes.php') ?: [],
        glob($root . '/app/zoosper-*/config/api_routes.php') ?: [],
        glob($root . '/app/zoosper-*/config/frontend_routes.php') ?: [],
        glob($root . '/packages/zoosper-*/config/admin_routes.php') ?: [],
        glob($root . '/packages/zoosper-*/config/api_routes.php') ?: [],
        glob($root . '/packages/zoosper-*/config/frontend_routes.php') ?: [],
    );
    $routes = '';
    foreach ($routeFiles as $file) { $routes .= (string) file_get_contents($file); }
    expect($routeFiles)->not->toBeEmpty();
    foreach (['/admin/login', '/admin', '/admin/pages', '/admin/media', '/admin/settings', '/admin/sites', '/admin/themes', '/api/v1/health'] as $path) {
        expect($routes)->toContain($path);
    }

    $settings = require $root . '/app/zoosper-settings/config/admin_assets.php';
    $auth = require $root . '/app/zoosper-auth/config/admin_assets.php';
    expect($settings)->toHaveKeys(['zoosper-settings-workspace-style', 'zoosper-settings-workspace-script'])
        ->and($auth)->toHaveKey('zoosper-admin-user-two-factor-reset-runtime')
        ->and($root . '/public/assets/admin/css/admin.css')->toBeFile()
        ->and($root . '/public/assets/brand/logo.svg')->toBeFile()
        ->and($root . '/public/assets/brand/favicon.svg')->toBeFile()
        ->and($root . '/public/assets/admin/js/editorjs.bundle.js')->toBeFile();
});










