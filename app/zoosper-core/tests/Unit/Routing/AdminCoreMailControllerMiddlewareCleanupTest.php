<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Routing;

use Zoosper\Core\Routing\ModuleRouteDefinition;

/**
 * Phase 1.33d-b guard: auth middleware owns core admin and mail-log permission
 * gates. Controllers may still read the current user for layout rendering, but
 * must not duplicate route-level permission decisions.
 */

/** @return array<string, list<string>> */
function adminCoreMailControllerCleanupRoutePermissions(): array
{
    $root = dirname(__DIR__, 5);
    $map = [];

    foreach ([
        $root . '/app/zoosper-admin/config/admin_routes.php',
        $root . '/app/zoosper-mail/config/admin_routes.php',
    ] as $file) {
        $config = require $file;
        foreach (is_array($config) ? $config : [] as $route) {
            if (!is_array($route)) {
                continue;
            }

            $map[strtoupper((string) ($route['method'] ?? 'GET')) . ' ' . (string) ($route['path'] ?? '')]
                = ModuleRouteDefinition::normalisePermissions($route['permission'] ?? null);
        }
    }

    return $map;
}

test('core admin and mail log routes keep middleware permission coverage', function () {
    $map = adminCoreMailControllerCleanupRoutePermissions();

    expect($map)->toHaveKey('GET /admin');
    expect($map['GET /admin'])->toBe(['admin.access']);

    foreach (['GET /admin/audit-log', 'GET /admin/login-history'] as $route) {
        expect($map)->toHaveKey($route);
        expect($map[$route])->toBe(['role.manage']);
    }

    foreach (['GET /admin/mail-logs', 'GET /admin/mail-logs/view'] as $route) {
        expect($map)->toHaveKey($route);
        expect($map[$route])->toContain('role.manage');
        expect($map[$route])->toContain('settings.manage');
    }
});

test('core admin and mail log controllers no longer duplicate permission gates', function () {
    $root = dirname(__DIR__, 5);
    foreach ([
        'app/zoosper-admin/src/Controller/AuditLogController.php',
        'app/zoosper-admin/src/Controller/DashboardController.php',
        'app/zoosper-admin/src/Controller/LoginHistoryController.php',
        'app/zoosper-mail/src/Controller/EmailLogAdminController.php',
    ] as $relative) {
        $source = (string) file_get_contents($root . '/' . $relative);

        expect($source)->not->toContain('requirePermission(');
        expect($source)->toContain('currentAdminUser()');
    }
});

test('dashboard still generates csrf token data for admin templates', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/app/zoosper-admin/src/Controller/DashboardController.php');

    expect($source)->toContain('$this->csrf->token()');
});
