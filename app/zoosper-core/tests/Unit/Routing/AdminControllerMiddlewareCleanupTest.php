<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Routing;

use Zoosper\Core\Routing\ModuleRouteDefinition;

/**
 * Phase 1.33d-a guard: auth middleware owns user/role admin permission gates.
 * Controllers may still read the current user for layout/audit/save attribution,
 * but they must not duplicate route-level permission decisions.
 */

/** @return array<string, list<string>> */
function adminControllerCleanupRoutePermissions(): array
{
    $root = dirname(__DIR__, 5);
    $file = $root . '/app/zoosper-auth/config/admin_routes.php';
    $config = require $file;
    $map = [];

    foreach (is_array($config) ? $config : [] as $route) {
        if (!is_array($route)) {
            continue;
        }

        $map[strtoupper((string) ($route['method'] ?? 'GET')) . ' ' . (string) ($route['path'] ?? '')]
            = ModuleRouteDefinition::normalisePermissions($route['permission'] ?? null);
    }

    return $map;
}

test('user and role admin routes keep middleware permission coverage', function () {
    $map = adminControllerCleanupRoutePermissions();

    foreach (['GET /admin/users', 'POST /admin/users/create', 'POST /admin/users/edit'] as $route) {
        expect($map)->toHaveKey($route);
        expect($map[$route])->toContain('role.manage');
        expect($map[$route])->toContain('user.manage');
    }

    foreach (['GET /admin/roles', 'POST /admin/roles/create', 'POST /admin/roles/edit'] as $route) {
        expect($map)->toHaveKey($route);
        expect($map[$route])->toBe(['role.manage']);
    }
});

test('user and role admin controllers no longer duplicate permission gates', function () {
    $root = dirname(__DIR__, 5);
    foreach ([
        'app/zoosper-admin/src/Controller/UserAdminController.php',
        'app/zoosper-admin/src/Controller/RoleAdminController.php',
    ] as $relative) {
        $source = (string) file_get_contents($root . '/' . $relative);

        expect($source)->not->toContain('requirePermission(');
        expect($source)->not->toContain('requireUserManager');
        expect($source)->not->toContain('requireRoleManager');
        expect($source)->toContain('currentAdminUser()');
    }
});

test('user and role admin forms still generate csrf tokens for middleware validation', function () {
    $root = dirname(__DIR__, 5);

    $userController = (string) file_get_contents($root . '/app/zoosper-admin/src/Controller/UserAdminController.php');
    $roleController = (string) file_get_contents($root . '/app/zoosper-admin/src/Controller/RoleAdminController.php');

    expect($userController)->toContain('$this->csrf->token()');
    expect($roleController)->toContain('$this->csrf->token()');
});
