<?php

declare(strict_types=1);

it('declares every Media Admin route and menu permission in the Media ACL manifest', function (): void {
    $root = dirname(__DIR__, 3);
    $acl = require $root . '/config/acl.php';
    $routes = require $root . '/config/admin_routes.php';
    $menu = require $root . '/config/admin_menu.php';

    $declared = array_keys($acl['permissions'] ?? []);
    $used = [];
    foreach ($routes as $route) {
        foreach ((array) ($route['permission'] ?? []) as $permission) {
            if (str_starts_with((string) $permission, 'media.')) {
                $used[] = (string) $permission;
            }
        }
    }
    foreach ($menu as $item) {
        if (str_starts_with((string) ($item['permission'] ?? ''), 'media.')) {
            $used[] = (string) $item['permission'];
        }
    }

    expect(array_values(array_unique($used)))->toBe(['media.manage'])
        ->and($declared)->toContain('media.manage');
});
