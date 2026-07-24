<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumRuntimeBridge;

it('exports route and menu definitions according to current metadata state', function (): void {
    $root = dirname(__DIR__, 5);
    $bridge = new PageMomentumRuntimeBridge();
    $routeConfig = require $root . '/app/zoosper-page/config/admin_page_momentum_routes.php';
    $menuConfig = require $root . '/app/zoosper-page/config/admin_page_momentum_menu.php';

    $definitions = $bridge->definitions($routeConfig, $menuConfig);
    $expectedCount = (($routeConfig['page_momentum_routes']['enabled'] ?? false) === true
        && ($menuConfig['page_momentum_menu']['enabled'] ?? false) === true) ? 1 : 0;

    expect($definitions['routeCount'])->toBe($expectedCount);
    expect($definitions['menuCount'])->toBe($expectedCount);
});

it('can export page momentum route and menu definitions in a fixture-enabled config only', function (): void {
    $root = dirname(__DIR__, 5);
    $bridge = new PageMomentumRuntimeBridge();
    $routeConfig = require $root . '/app/zoosper-page/config/admin_page_momentum_routes.php';
    $menuConfig = require $root . '/app/zoosper-page/config/admin_page_momentum_menu.php';

    $routeConfig['page_momentum_routes']['enabled'] = true;
    $menuConfig['page_momentum_menu']['enabled'] = true;

    $definitions = $bridge->definitions($routeConfig, $menuConfig);

    expect($definitions['routeCount'])->toBe(1);
    expect($definitions['menuCount'])->toBe(1);
    expect($definitions['routes'][0]['name'])->toBe('admin.page_momentum.index');
    expect($definitions['menuItems'][0]['route'])->toBe('admin.page_momentum.index');
});

it('keeps page momentum runtime bridge proof tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/prove-page-admin-momentum-runtime-bridge.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-runtime-bridge.php')->toBeFile();
});
