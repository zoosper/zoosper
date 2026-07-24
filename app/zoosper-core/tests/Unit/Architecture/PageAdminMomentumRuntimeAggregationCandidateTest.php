<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminRuntimeAggregationProvider;

it('provides one page momentum route and menu item without live mutation', function (): void {
    $root = dirname(__DIR__, 5);
    $config = require $root . '/app/zoosper-page/config/admin_page_momentum_runtime_aggregation_candidate.php';
    $hookCandidate = require $root . '/app/zoosper-page/config/admin_page_momentum_hook_candidate.php';

    $provided = (new PageMomentumAdminRuntimeAggregationProvider())->provide($config, $hookCandidate);

    expect($provided['enabled'])->toBeTrue();
    expect($provided['routeCount'])->toBe(1);
    expect($provided['menuCount'])->toBe(1);
    expect($provided['routes'][0]['name'])->toBe('admin.page_momentum.index');
    expect($provided['menuItems'][0]['route'])->toBe('admin.page_momentum.index');
    expect($provided['liveMutation'])->toBeFalse();
});

it('keeps phase 1.54 runtime aggregation tooling available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/prove-page-admin-momentum-runtime-aggregation-candidate.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-runtime-aggregation-readiness.php')->toBeFile();
});
