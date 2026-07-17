<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Routing;

use Zoosper\Core\Routing\ModuleRouteDefinition;

/**
 * Phase 1.33d-c guard: auth middleware owns page/theme admin permission gates.
 * Controllers may still read the current user for rendering, event/audit/save
 * attribution and translation context, but must not duplicate route-level
 * permission decisions.
 */

/** @return array<string, list<string>> */
function pageThemeControllerCleanupRoutePermissions(): array
{
    $root = dirname(__DIR__, 5);
    $map = [];

    foreach ([
        $root . '/app/zoosper-page/config/admin_routes.php',
        $root . '/app/zoosper-theme/config/admin_routes.php',
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

test('page and theme admin routes keep middleware permission coverage', function () {
    $map = pageThemeControllerCleanupRoutePermissions();

    foreach ([
        'GET /admin/pages',
        'GET /admin/pages/create',
        'POST /admin/pages/create',
        'GET /admin/pages/edit',
        'POST /admin/pages/edit',
        'GET /admin/pages/preview',
        'POST /admin/pages/publish',
        'POST /admin/pages/unpublish',
    ] as $route) {
        expect($map)->toHaveKey($route);
        expect($map[$route])->toBe(['page.manage']);
    }

    foreach (['GET /admin/themes', 'POST /admin/themes/assign'] as $route) {
        expect($map)->toHaveKey($route);
        expect($map[$route])->toBe(['settings.manage']);
    }
});

test('page and theme admin controllers no longer duplicate permission gates', function () {
    $root = dirname(__DIR__, 5);
    foreach ([
        'app/zoosper-admin/src/Controller/PageAdminController.php',
        'app/zoosper-admin/src/Controller/ThemeAdminController.php',
    ] as $relative) {
        $source = (string) file_get_contents($root . '/' . $relative);

        expect($source)->not->toContain('requirePermission(');
        expect($source)->not->toContain('requirePageManager');
        expect($source)->toContain('currentAdminUser()');
    }
});

test('page and theme admin forms still generate csrf tokens for middleware validation', function () {
    $root = dirname(__DIR__, 5);

    $pageController = (string) file_get_contents($root . '/app/zoosper-admin/src/Controller/PageAdminController.php');
    $themeController = (string) file_get_contents($root . '/app/zoosper-admin/src/Controller/ThemeAdminController.php');

    expect($pageController)->toContain('$this->csrf->token()');
    expect($themeController)->toContain('$this->csrf->token()');
});
