<?php

declare(strict_types=1);

it('contributes the existing Email Logs screen under System without changing its route', function (): void {
    $module = dirname(__DIR__, 3);
    $menu = require $module . '/config/admin_menu.php';
    $routes = require $module . '/config/admin_routes.php';

    expect($menu)->toBe([[
        'code' => 'mail-logs',
        'label' => 'Email Logs',
        'url' => '/admin/mail-logs',
        'permission' => 'settings.manage',
        'sort_order' => 40,
        'group' => 'System',
        'icon' => 'envelope',
    ]]);

    $index = array_values(array_filter(
        $routes,
        static fn (array $route): bool => ($route['method'] ?? null) === 'GET'
            && ($route['path'] ?? null) === '/admin/mail-logs',
    ));

    expect($index)->toHaveCount(1)
        ->and($index[0]['action'] ?? null)->toBe('index')
        ->and($index[0]['permission'] ?? null)->toBe(['role.manage', 'settings.manage']);
});
