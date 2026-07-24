<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminIntegrationPreview;

it('keeps page momentum integration preview aligned with current metadata state', function (): void {
    $root = dirname(__DIR__, 5);
    $previewer = new PageMomentumAdminIntegrationPreview();
    $routeConfig = require $root . '/app/zoosper-page/config/admin_page_momentum_routes.php';
    $menuConfig = require $root . '/app/zoosper-page/config/admin_page_momentum_menu.php';

    $preview = $previewer->preview($routeConfig, $menuConfig);
    $expectedCount = (($routeConfig['page_momentum_routes']['enabled'] ?? false) === true
        && ($menuConfig['page_momentum_menu']['enabled'] ?? false) === true) ? 1 : 0;

    expect($preview['routeCount'])->toBe($expectedCount);
    expect($preview['menuCount'])->toBe($expectedCount);
    expect($preview['liveMutation'])->toBeFalse();
});

it('can preview a future enabled route and menu without live mutation', function (): void {
    $root = dirname(__DIR__, 5);
    $previewer = new PageMomentumAdminIntegrationPreview();
    $routeConfig = require $root . '/app/zoosper-page/config/admin_page_momentum_routes.php';
    $menuConfig = require $root . '/app/zoosper-page/config/admin_page_momentum_menu.php';

    $routeConfig['page_momentum_routes']['enabled'] = true;
    $menuConfig['page_momentum_menu']['enabled'] = true;

    $preview = $previewer->preview($routeConfig, $menuConfig);

    expect($preview['routeCount'])->toBe(1);
    expect($preview['menuCount'])->toBe(1);
    expect($preview['liveMutation'])->toBeFalse();
    expect($preview['wouldRegisterRoutes'][0]['name'])->toBe('admin.page_momentum.index');
});

it('keeps Phase 1.47 closure tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/prove-page-admin-momentum-integration-preview.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-phase-147-closure.php')->toBeFile();
});
