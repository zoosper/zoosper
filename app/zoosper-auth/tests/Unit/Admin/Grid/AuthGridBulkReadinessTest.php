<?php

declare(strict_types=1);

use Zoosper\Auth\Admin\Grid\AdminUserGridIndex;
use Zoosper\Auth\Admin\Grid\AuthGridPagePresenter;
use Zoosper\Auth\Admin\Grid\RoleGridIndex;

it('registers both index façades and the presenter in the active Auth Grid fragment', function (): void {
    $root = dirname(__DIR__, 6);
    $services = require $root . '/app/zoosper-auth/config/services_auth_grid.php';

    expect($services)->toHaveKeys([
        AuthGridPagePresenter::class,
        AdminUserGridIndex::class,
        RoleGridIndex::class,
    ]);
});

it('keeps index façades authenticated, request-global free, and read-side only', function (): void {
    $root = dirname(__DIR__, 6);
    foreach (['AdminUserGridIndex.php', 'RoleGridIndex.php'] as $file) {
        $source = (string) file_get_contents($root . '/app/zoosper-auth/src/Admin/Grid/' . $file);
        expect($source)->toContain('int $authenticatedAdminUserId')
            ->not->toContain('$_GET')
            ->not->toContain('$_POST')
            ->not->toContain('PasswordHasher')
            ->not->toContain('CsrfTokenManager');
    }
});

it('rejects external primary-action URLs', function (): void {
    $page = (new ReflectionClass(\Zoosper\Auth\Admin\Grid\AuthGridPage::class))->newInstanceWithoutConstructor();
    expect(fn () => (new AuthGridPagePresenter())->present($page, 'https://example.test', 'Create'))
        ->toThrow(InvalidArgumentException::class);
});


it('renders escaped admin-local create actions as established primary buttons', function (): void {
    $root = dirname(__DIR__, 6);
    $presenter = (string) file_get_contents(
        $root . '/app/zoosper-auth/src/Admin/Grid/AuthGridPagePresenter.php',
    );

    expect($presenter)->toContain('<a class="button" href="')
        ->toContain('$this->escape($createUrl)')
        ->toContain('$this->escape($createLabel)')
        ->not->toContain('style=')
        ->not->toContain('onclick=');
});
