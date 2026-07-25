<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminRouteMenuHook;

it('keeps the page momentum route menu hook available', function (): void {
    expect(class_exists(PageMomentumAdminRouteMenuHook::class))->toBeTrue();
});

it('exports page momentum route and menu items from hook input', function (): void {
    $hook = new PageMomentumAdminRouteMenuHook();
    $export = $hook->export([
        'routes' => [[
            'name' => 'admin.page_momentum.index',
            'path' => '/admin/page-momentum',
        ]],
    ], [
        'menuItems' => [[
            'route' => 'admin.page_momentum.index',
        ]],
    ]);

    expect($export)->toBeArray();
    expect($export['routes'] ?? [])->toBeArray();
    expect($export['menuItems'] ?? [])->toBeArray();
});
