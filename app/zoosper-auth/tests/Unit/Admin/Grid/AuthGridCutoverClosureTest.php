<?php

declare(strict_types=1);

it('keeps both live Auth list actions on their feature Grid façades', function (): void {
    $root = dirname(__DIR__, 6);
    $expectations = [
        'UserAdminController.php' => [
            'AdminUserGridIndex',
            "'Admin Users'",
            "'zoosper-auth::admin/users/index'",
            "'admin-users'",
        ],
        'RoleAdminController.php' => [
            'RoleGridIndex',
            "'Roles & Permissions'",
        ],
    ];

    foreach ($expectations as $file => $signals) {
        $source = (string) file_get_contents(
            $root . '/app/zoosper-auth/src/Admin/Controller/' . $file,
        );

        foreach ($signals as $signal) {
            expect($source)->toContain($signal);
        }

        expect($source)->toContain('AuthGridQueryState::fromQuery($_GET)')
            ->toContain('AuthGridQueryState::bookmarkId($_GET)')
            ->toContain('if ($this->gridIndex !== null)');
    }
});

it('preserves all Auth write actions during the listing cutover', function (): void {
    $root = dirname(__DIR__, 6);
    $controllers = [
        'UserAdminController.php' => ['createForm', 'create', 'editForm', 'update'],
        'RoleAdminController.php' => ['createForm', 'create', 'editForm', 'update'],
    ];

    foreach ($controllers as $file => $methods) {
        $source = (string) file_get_contents(
            $root . '/app/zoosper-auth/src/Admin/Controller/' . $file,
        );

        foreach ($methods as $method) {
            expect($source)->toContain('function ' . $method . '(');
        }
    }
});

it('keeps route permissions and write endpoints unchanged', function (): void {
    $root = dirname(__DIR__, 6);
    $routes = (string) file_get_contents(
        $root . '/app/zoosper-auth/config/admin_routes.php',
    );

    expect($routes)->toContain("'path' => '/admin/users'")
        ->toContain("'permission' => ['role.manage', 'user.manage']")
        ->toContain("'path' => '/admin/users/create'")
        ->toContain("'path' => '/admin/users/edit'")
        ->toContain("'path' => '/admin/roles'")
        ->toContain("'path' => '/admin/roles/create'")
        ->toContain("'path' => '/admin/roles/edit'");
});

it('has no committed one-off Auth Grid apply helpers', function (): void {
    $root = dirname(__DIR__, 6);

    expect($root . '/tools/apply-auth-grid-service-registration.php')->not->toBeFile()
        ->and($root . '/tools/apply-auth-grid-live-cutover.php')->not->toBeFile();
});










