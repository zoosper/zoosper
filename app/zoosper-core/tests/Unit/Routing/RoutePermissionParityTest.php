<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Routing;

use Zoosper\Core\Routing\ModuleRouteDefinition;

/**
 * Phase 1.33b lockstep guard: the real admin route configs must declare BOTH
 * permissions for the OR-routes, so reverting one to a single string fails here.
 *
 * @return array<string, list<string>> "METHOD path" => permissions
 */
function adminRoutePermissionMap(): array
{
    $root = dirname(__DIR__, 5);
    $map = [];

    foreach (glob($root . '/app/*/config/admin_routes.php') ?: [] as $file) {
        $config = require $file;
        if (!is_array($config)) {
            continue;
        }

        foreach ($config as $route) {
            if (!is_array($route)) {
                continue;
            }
            $method = strtoupper((string) ($route['method'] ?? 'GET'));
            $path = (string) ($route['path'] ?? '');
            $map[$method . ' ' . $path] = ModuleRouteDefinition::normalisePermissions($route['permission'] ?? null);
        }
    }

    return $map;
}

test('user admin routes accept role.manage OR user.manage', function () {
    $map = adminRoutePermissionMap();

    foreach (['GET /admin/users', 'POST /admin/users/create', 'POST /admin/users/edit'] as $route) {
        expect($map)->toHaveKey($route);
        expect($map[$route])->toContain('role.manage');
        expect($map[$route])->toContain('user.manage');
    }
});

test('mail log routes accept role.manage OR settings.manage', function () {
    $map = adminRoutePermissionMap();

    foreach (['GET /admin/mail-logs', 'GET /admin/mail-logs/view'] as $route) {
        expect($map)->toHaveKey($route);
        expect($map[$route])->toContain('role.manage');
        expect($map[$route])->toContain('settings.manage');
    }
});

test('single-permission admin routes still normalise to one permission', function () {
    $map = adminRoutePermissionMap();

    expect($map)->toHaveKey('GET /admin/roles');
    expect($map['GET /admin/roles'])->toBe(['role.manage']);
});