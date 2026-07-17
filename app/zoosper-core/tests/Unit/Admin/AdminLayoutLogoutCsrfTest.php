<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Admin;

test('admin layout logout form includes csrf token for central csrf middleware', function () {
    $root = dirname(__DIR__, 5);
    $layout = (string) file_get_contents($root . '/app/zoosper-admin/src/Layout/AdminLayout.php');
    $services = (string) file_get_contents($root . '/app/zoosper-admin/config/services.php');

    expect($layout)->toContain('CsrfTokenManager');
    expect($layout)->toContain('name="_csrf_token"');
    expect($layout)->toContain('$this->csrf->token()');
    expect($services)->toContain('$services->get(CsrfTokenManager::class)');
});

test('admin logout route remains post only and protected by middleware', function () {
    $root = dirname(__DIR__, 5);
    $routes = require $root . '/app/zoosper-admin/config/admin_routes.php';
    $logout = null;

    foreach ($routes as $route) {
        if (($route['path'] ?? '') === '/admin/logout') {
            $logout = $route;
            break;
        }
    }

    expect($logout)->not->toBeNull();
    expect($logout['method'])->toBe('POST');
    expect($logout['permission'])->toBe('admin.access');
});
