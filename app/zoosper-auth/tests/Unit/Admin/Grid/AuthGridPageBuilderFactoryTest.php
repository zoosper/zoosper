<?php

declare(strict_types=1);

use Zoosper\AdminGrid\GridBookmarkRepository;
use Zoosper\AdminGrid\GridPreferenceRepository;
use Zoosper\AdminGrid\GridStateNormaliser;
use Zoosper\AdminGrid\GridViewStateResolver;
use Zoosper\Auth\Admin\Grid\AdminUserGridPageBuilder;
use Zoosper\Auth\Admin\Grid\AuthGridPageBuilderFactory;
use Zoosper\Auth\Admin\Grid\RoleGridPageBuilder;
use Zoosper\Grid\GridColumnOrderer;

it('constructs both complete Auth Grid page-builder graphs', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $resolver = new GridViewStateResolver(
        preferences: new GridPreferenceRepository($pdo),
        bookmarks: new GridBookmarkRepository($pdo),
        normaliser: new GridStateNormaliser(),
        columnOrderer: new GridColumnOrderer(),
    );

    $factory = new AuthGridPageBuilderFactory($pdo, $resolver);

    expect($factory->adminUsers())->toBeInstanceOf(AdminUserGridPageBuilder::class)
        ->and($factory->roles())->toBeInstanceOf(RoleGridPageBuilder::class);
});

it('keeps the factory on the Auth Grid read side only', function (): void {
    $root = dirname(__DIR__, 6);
    $source = (string) file_get_contents(
        $root . '/app/zoosper-auth/src/Admin/Grid/AuthGridPageBuilderFactory.php',
    );

    expect($source)->toContain('PdoAdminUserGridReadRepository')
        ->toContain('PdoRoleGridReadRepository')
        ->toContain('GridViewStateResolver')
        ->not->toContain('PasswordHasher')
        ->not->toContain('CsrfTokenManager')
        ->not->toContain('SessionGuard')
        ->not->toContain('$_GET')
        ->not->toContain('$_POST');
});










