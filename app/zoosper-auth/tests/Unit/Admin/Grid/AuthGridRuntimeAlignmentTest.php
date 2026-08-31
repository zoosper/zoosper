<?php

declare(strict_types=1);

use Zoosper\Auth\Admin\Grid\AdminUserGridIndex;
use Zoosper\Auth\Admin\Grid\AuthGridPagePresenter;
use Zoosper\Auth\Admin\Grid\RoleGridIndex;

it('keeps the active Auth Grid services and controller wiring aligned', function (): void {
    $root = dirname(__DIR__, 6);
    $services = require $root . '/app/zoosper-auth/config/services_auth_grid.php';
    $controllers = (string) file_get_contents(
        $root . '/app/zoosper-auth/config/controllers.php',
    );

    expect($services)->toHaveKeys([
        AuthGridPagePresenter::class,
        AdminUserGridIndex::class,
        RoleGridIndex::class,
    ])->and($controllers)->toContain('gridIndex: $services->get(AdminUserGridIndex::class)')
        ->toContain('gridIndex: $services->get(RoleGridIndex::class)');
});

it('keeps Admin Users Grid HTML inside the trusted template branch', function (): void {
    $root = dirname(__DIR__, 6);
    $template = (string) file_get_contents(
        $root . '/app/zoosper-auth/resources/views/admin/users/index.latte',
    );

    expect($template)->toContain('{if isset($gridHtml)}')
        ->toContain('{$gridHtml|noescape}')
        ->toContain('{else}')
        ->toContain('{/if}');
});










