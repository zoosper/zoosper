<?php

declare(strict_types=1);

use Zoosper\Auth\Admin\Grid\AdminUserGridIndex;
use Zoosper\Auth\Admin\Grid\AdminUserGridPageBuilder;
use Zoosper\Auth\Admin\Grid\AuthGridPageBuilderFactory;
use Zoosper\Auth\Admin\Grid\AuthGridPagePresenter;
use Zoosper\Auth\Admin\Grid\RoleGridIndex;
use Zoosper\Auth\Admin\Grid\RoleGridPageBuilder;

it('declares the complete Auth Grid read-side service fragment', function (): void {
    $root = dirname(__DIR__, 6);
    $services = require $root . '/app/zoosper-auth/config/services_auth_grid.php';

    expect($services)->toBeArray()
        ->and($services)->toHaveKeys([
            AuthGridPageBuilderFactory::class,
            AdminUserGridPageBuilder::class,
            RoleGridPageBuilder::class,
            AuthGridPagePresenter::class,
            AdminUserGridIndex::class,
            RoleGridIndex::class,
        ]);

    foreach ($services as $factory) {
        expect($factory)->toBeInstanceOf(Closure::class);
    }
});

it('keeps the service fragment read-side only', function (): void {
    $root = dirname(__DIR__, 6);
    $source = (string) file_get_contents(
        $root . '/app/zoosper-auth/config/services_auth_grid.php',
    );

    expect($source)->toContain('GridViewStateResolver::class')
        ->toContain('AuthGridPageBuilderFactory::class')
        ->toContain('AdminUserGridIndex::class')
        ->toContain('RoleGridIndex::class')
        ->not->toContain('PasswordHasher')
        ->not->toContain('CsrfTokenManager')
        ->not->toContain('SessionGuard')
        ->not->toContain('AdminUserRepository')
        ->not->toContain('RoleRepository');
});










