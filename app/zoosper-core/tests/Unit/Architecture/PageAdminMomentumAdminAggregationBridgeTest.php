<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminAggregationBridge;

it('exports one route and one menu item from the page momentum candidate without live mutation', function (): void {
    $root = dirname(__DIR__, 5);
    $candidate = require $root . '/app/zoosper-page/config/admin_page_momentum_runtime_candidate.php';

    $export = (new PageMomentumAdminAggregationBridge())->export($candidate);

    expect($export['routeCount'])->toBe(1);
    expect($export['menuCount'])->toBe(1);
    expect($export['routes'][0]['name'])->toBe('admin.page_momentum.index');
    expect($export['menuItems'][0]['route'])->toBe('admin.page_momentum.index');
    expect($export['liveMutation'])->toBeFalse();
});

it('keeps phase 1.51 bridge tooling available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/prove-page-admin-momentum-admin-aggregation-bridge.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-admin-aggregation-bridge.php')->toBeFile();
});
