<?php

declare(strict_types=1);

it('exposes identity lifecycle only through protected POST routes', function (): void {
    $routes = require dirname(__DIR__, 3) . '/config/admin_routes.php';
    $expected = [
        '/admin/users/{id:\\d+}/disable' => 'user.manage',
        '/admin/users/{id:\\d+}/restore' => 'user.manage',
        '/admin/roles/{id:\\d+}/delete' => 'role.manage',
    ];
    foreach ($expected as $path => $permission) {
        $matches = array_values(array_filter($routes, static fn(array $route): bool => ($route['path'] ?? '') === $path));
        expect($matches)->toHaveCount(1)
            ->and($matches[0]['method'])->toBe('POST')
            ->and($matches[0]['permission'])->toBe($permission);
    }
});

it('keeps identity lifecycle persistence out of Auth controllers and presentation CSP safe', function (): void {
    $root = dirname(__DIR__, 3);
    $users = (string) file_get_contents($root . '/src/Admin/Controller/UserAdminController.php');
    $roles = (string) file_get_contents($root . '/src/Admin/Controller/RoleAdminController.php');
    $userResponder = (string) file_get_contents($root . '/src/Admin/Lifecycle/AdminUserLifecycleAdminResponder.php');
    $roleResponder = (string) file_get_contents($root . '/src/Admin/Lifecycle/RoleLifecycleAdminResponder.php');
    expect($users)->toContain('AdminUserLifecycleAdminResponder')->not->toContain('DELETE FROM admin_users')
        ->and($roles)->toContain('RoleLifecycleAdminResponder')->not->toContain('DELETE FROM admin_roles')
        ->and($userResponder)->toContain('_csrf_token')->not->toContain('onclick=')->not->toContain('confirm(')
        ->and($roleResponder)->toContain('_csrf_token')->not->toContain('onclick=')->not->toContain('confirm(');
});
