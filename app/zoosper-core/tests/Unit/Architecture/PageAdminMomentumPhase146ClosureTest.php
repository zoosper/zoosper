<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
use Zoosper\Page\Admin\PageMomentumDefinitionProvider;

it('normalises page momentum route and menu definitions according to current metadata state', function (): void {
    $root = dirname(__DIR__, 5);
    $provider = new PageMomentumDefinitionProvider();

    $routeConfig = require $root . '/app/zoosper-page/config/admin_page_momentum_routes.php';
    $menuConfig = require $root . '/app/zoosper-page/config/admin_page_momentum_menu.php';
    $definitions = $provider->definitions($routeConfig, $menuConfig);

    $expectedEnabled = ($routeConfig['page_momentum_routes']['enabled'] ?? false) === true
        && ($menuConfig['page_momentum_menu']['enabled'] ?? false) === true;

    expect($definitions['enabled'])->toBe($expectedEnabled);
    expect($definitions['routes'])->toBeArray()->toHaveCount(1);
    expect($definitions['menuItems'])->toBeArray()->toHaveCount(1);
    expect($definitions['routes'][0]['controller'])->toBe(PageMomentumAdminController::class);
});

it('keeps Phase 1.46 closure tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/prove-page-admin-momentum-definition-provider.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-runtime-bridge-readiness.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-phase-146-closure.php')->toBeFile();
});

it('documents current runtime route and menu status', function (): void {
    $root = dirname(__DIR__, 5);
    $doc = $root . '/docs/development/page-admin-momentum-phase-1.46-closure.md';

    expect($doc)->toBeFile();
    $contents = (string) file_get_contents($doc);
    expect($contents)->toContain('Runtime route');
    expect($contents)->toContain('Admin menu');
});
