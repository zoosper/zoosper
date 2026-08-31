<?php

declare(strict_types=1);

it('owns authenticated POST-only current-user Dashboard preference mutations', function (): void {
    $root = dirname(__DIR__, 5);
    $routes = require $root . '/app/zoosper-admin/config/admin_routes.php';
    $map = [];
    foreach ($routes as $route) {
        $map[$route['method'] . ' ' . $route['path']] = $route;
    }
    $controller = (string) file_get_contents($root . '/app/zoosper-admin/src/Controller/DashboardController.php');
    $service = (string) file_get_contents($root . '/app/zoosper-admin/src/Dashboard/DashboardPersonalisationService.php');

    expect($map)->toHaveKeys([
        'POST /admin/dashboard/preferences',
        'POST /admin/dashboard/preferences/reset',
    ])->and($map['POST /admin/dashboard/preferences']['permission'])->toBe('admin.access')
        ->and($map['POST /admin/dashboard/preferences/reset']['permission'])->toBe('admin.access')
        ->and($controller)->toContain('$form = $request->form()')
        ->toContain('$this->currentAdminUser()')
        ->not->toContain('admin_user_id')
        ->not->toContain('ServiceContainer')
        ->and($service)->toContain('$this->widgets->forUser($user)')
        ->toContain('$this->preferences->findForUser($user->id)')
        ->toContain('$this->preferences->saveForUser($user->id');
});

it('keeps Dashboard personalisation escaped accessible CSP-safe and progressively enhanced', function (): void {
    $root = dirname(__DIR__, 5);
    $moduleView = (string) file_get_contents($root . '/app/zoosper-admin/resources/views/dashboard/index.php');
    $themeView = (string) file_get_contents($root . '/themes/admin/default/templates/modules/zoosper-admin/dashboard/index.php');
    $script = (string) file_get_contents($root . '/app/zoosper-admin/resources/assets/js/dashboard-personalisation.js');
    $css = (string) file_get_contents($root . '/app/zoosper-admin/resources/assets/css/admin-components.css');
    $assets = require $root . '/app/zoosper-admin/config/admin_assets.php';

    expect($moduleView)->toBe($themeView)
        ->toContain('name="_csrf_token"')
        ->toContain('name="visible_widgets[]"')
        ->toContain('name="widget_order[]"')
        ->toContain('data-dashboard-move="up"')
        ->toContain('aria-live="polite"')
        ->not->toMatch('/\son[a-z]+\s*=/i')
        ->not->toContain('<script')
        ->and($script)->toContain("'use strict'")
        ->toContain('addEventListener')
        ->toContain('CSS.escape')
        ->not->toContain('innerHTML')
        ->and($css)->toContain('.dashboard-personalisation')
        ->toContain('@media (prefers-reduced-motion: reduce)')
        ->and($assets['assets']['zoosper-dashboard-personalisation-script']['screens'])->toBe(['dashboard', 'dashboard-role-defaults'])
        ->and($assets['assets']['zoosper-dashboard-personalisation-script']['attributes']['defer'])->toBeTrue();
});

it('protects role-default management with role.manage POST CSRF routes and CSP-safe templates', function (): void {
    $root = dirname(__DIR__, 5);
    $routes = require $root . '/app/zoosper-admin/config/admin_routes.php';
    $map = [];
    foreach ($routes as $route) {
        $map[$route['method'] . ' ' . $route['path']] = $route;
    }
    $moduleView = (string) file_get_contents($root . '/app/zoosper-admin/resources/views/dashboard/role-defaults.php');
    $themeView = (string) file_get_contents($root . '/themes/admin/default/templates/modules/zoosper-admin/dashboard/role-defaults.php');

    expect($map)->toHaveKeys([
        'GET /admin/dashboard/role-defaults',
        'POST /admin/dashboard/role-defaults',
        'POST /admin/dashboard/role-defaults/reset',
    ])->and($map['GET /admin/dashboard/role-defaults']['permission'])->toBe('role.manage')
        ->and($map['POST /admin/dashboard/role-defaults']['permission'])->toBe('role.manage')
        ->and($map['POST /admin/dashboard/role-defaults/reset']['permission'])->toBe('role.manage')
        ->and($moduleView)->toBe($themeView)
        ->toContain('name="_csrf_token"')
        ->toContain('name="role_id"')
        ->toContain('Permissions remain authoritative')
        ->not->toMatch('/\son[a-z]+\s*=/i')
        ->not->toContain('<script');
});

it('declares an Auth-owned role-isolated Dashboard preference table with cascade cleanup', function (): void {
    $root = dirname(__DIR__, 5);
    $schema = require $root . '/app/zoosper-auth/config/db_schema.php';
    $table = $schema['tables']['admin_role_dashboard_preferences'];

    expect($table['columns'])->toHaveKeys(['role_id', 'hidden_widget_codes_json', 'widget_order_json', 'updated_at'])
        ->and($table['columns']['role_id']['primary'])->toBeTrue()
        ->and($table['foreign_keys']['fk_admin_role_dashboard_preferences_role']['on_delete'])->toBe('CASCADE');
});

it('declares an Admin-owned user-isolated Dashboard preference table', function (): void {
    $root = dirname(__DIR__, 5);
    $schema = require $root . '/app/zoosper-admin/config/db_schema.php';
    $table = $schema['tables']['admin_dashboard_preferences'];

    expect($table['columns'])->toHaveKeys([
        'admin_user_id',
        'hidden_widget_codes_json',
        'widget_order_json',
        'updated_at',
    ])->and($table['indexes']['idx_admin_dashboard_preferences_user'])->toBe([
        'columns' => ['admin_user_id'],
        'unique' => true,
    ]);
});










