<?php

declare(strict_types=1);

it('adopts canonical admin URLs across Auth CRUD controllers and Grid runtime', function (): void {
    $root = dirname(__DIR__, 5);
    $user = (string) file_get_contents($root . '/app/zoosper-auth/src/Admin/Controller/UserAdminController.php');
    $role = (string) file_get_contents($root . '/app/zoosper-auth/src/Admin/Controller/RoleAdminController.php');
    $presenter = (string) file_get_contents($root . '/app/zoosper-auth/src/Admin/Grid/AuthGridPagePresenter.php');
    $services = (string) file_get_contents($root . '/app/zoosper-auth/config/services_auth_grid.php');

    expect($user)->toContain('private ?AdminUrlGenerator $adminUrls = null')
        ->toContain("\$this->adminUrl('users/edit'")
        ->and($role)->toContain('private ?AdminUrlGenerator $adminUrls = null')
        ->toContain("\$this->adminUrl('roles/edit'")
        ->and($presenter)->toContain('$this->adminUrls?->isAdminPath($createUrl)')
        ->and($services)->toContain('$services->get(AdminUrlGenerator::class)');
});

it('removes migrated literal admin page URLs while retaining static asset URLs', function (): void {
    $root = dirname(__DIR__, 5);
    $files = [
        'app/zoosper-auth/resources/views/admin/users/index.latte',
        'app/zoosper-auth/resources/views/admin/users/form.latte',
        'app/zoosper-admin/resources/views/admin/roles/index.php',
        'app/zoosper-admin/resources/views/admin/roles/form.php',
    ];
    foreach ($files as $file) {
        expect((string) file_get_contents($root . '/' . $file))->not->toContain('href="/admin/');
    }
    $assets = (string) file_get_contents($root . '/app/zoosper-admin/resources/views/admin/roles/permission-tree.php');
    expect($assets)->toContain('/assets/admin/');
});
