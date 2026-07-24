<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumLiveDuplicateGuard;

it('detects exactly one page momentum route and menu item', function (): void {
    $guard = new PageMomentumLiveDuplicateGuard();
    $result = $guard->inspect(
        ['routes' => [[
            'name' => 'admin.page_momentum.index',
            'method' => 'GET',
            'path' => '/admin/page-momentum',
            'permission' => 'page.manage',
        ]]],
        ['items' => [[
            'label' => 'Page momentum',
            'route' => 'admin.page_momentum.index',
            'permission' => 'page.manage',
        ]]],
    );

    expect($result['routeMatches'])->toBe(1);
    expect($result['menuMatches'])->toBe(1);
    expect($result['ok'])->toBeTrue();
});

it('fails duplicate guard when route or menu is duplicated', function (): void {
    $guard = new PageMomentumLiveDuplicateGuard();
    $result = $guard->inspect(
        ['routes' => [
            ['name' => 'admin.page_momentum.index', 'path' => '/admin/page-momentum'],
            ['name' => 'admin.page_momentum.index', 'path' => '/admin/page-momentum'],
        ]],
        ['items' => [
            ['route' => 'admin.page_momentum.index'],
            ['route' => 'admin.page_momentum.index'],
        ]],
    );

    expect($result['ok'])->toBeFalse();
});

it('keeps phase 1.57 closure tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/audit-page-admin-momentum-live-duplicates.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-phase-157-closure.php')->toBeFile();
});
