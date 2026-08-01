<?php

declare(strict_types=1);

it('cuts both Auth list actions over while preserving legacy fallback paths', function (): void {
    $root = dirname(__DIR__, 6);
    foreach ([
        'UserAdminController.php' => 'AdminUserGridIndex',
        'RoleAdminController.php' => 'RoleGridIndex',
    ] as $file => $service) {
        $source = (string) file_get_contents($root . '/app/zoosper-auth/src/Admin/Controller/' . $file);
        expect($source)->toContain($service)
            ->toContain('AuthGridQueryState::fromQuery($_GET)')
            ->toContain('AuthGridQueryState::bookmarkId($_GET)')
            ->toContain('if ($this->gridIndex !== null)');
    }
});

it('wires both Grid index façades through the Auth controller factory', function (): void {
    $root = dirname(__DIR__, 6);
    $source = (string) file_get_contents($root . '/app/zoosper-auth/config/controllers.php');
    expect($source)->toContain('gridIndex: $services->get(AdminUserGridIndex::class)')
        ->toContain('gridIndex: $services->get(RoleGridIndex::class)');
});

it('renders the Admin Users Grid fragment without escaping trusted renderer HTML', function (): void {
    $root = dirname(__DIR__, 6);
    $source = (string) file_get_contents($root . '/app/zoosper-auth/resources/views/admin/users/index.latte');
    expect($source)->toContain('{if isset($gridHtml)}')
        ->toContain('{$gridHtml|noescape}')
        ->toContain('{else}');
});
