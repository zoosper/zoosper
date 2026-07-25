<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminAggregationBridge;

it('keeps the page momentum admin aggregation bridge available', function (): void {
    expect(class_exists(PageMomentumAdminAggregationBridge::class))->toBeTrue();
});

it('exports route and menu collections from aggregation bridge inputs', function (): void {
    $bridge = new PageMomentumAdminAggregationBridge();
    $export = $bridge->export([
        'routes' => [[
            'name' => 'admin.page_momentum.index',
            'path' => '/admin/page-momentum',
        ]],
        'menuItems' => [[
            'route' => 'admin.page_momentum.index',
        ]],
    ]);

    expect($export)->toBeArray();
    expect($export['routes'] ?? [])->toBeArray();
    expect($export['menuItems'] ?? [])->toBeArray();
});
