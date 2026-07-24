<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminRouteMenuHook;

it('exports one page momentum route and matching menu item without live mutation', function (): void {
    $root = dirname(__DIR__, 5);
    $runtimeConfig = require $root . '/app/zoosper-page/config/admin_page_momentum_runtime_aggregation_candidate.php';
    $hookCandidate = require $root . '/app/zoosper-page/config/admin_page_momentum_hook_candidate.php';

    $export = (new PageMomentumAdminRouteMenuHook())->export($runtimeConfig, $hookCandidate);

    expect($export['routeCount'])->toBe(1);
    expect($export['menuCount'])->toBe(1);
    expect($export['routes'][0]['name'])->toBe('admin.page_momentum.index');
    expect($export['menuItems'][0]['route'])->toBe('admin.page_momentum.index');
    expect($export['liveMutation'])->toBeFalse();
});

it('keeps phase 1.55 route menu hook tooling available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/prove-page-admin-momentum-route-menu-hook.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-route-menu-hook-readiness.php')->toBeFile();
});
