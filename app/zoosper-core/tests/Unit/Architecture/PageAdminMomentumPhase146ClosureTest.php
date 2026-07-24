<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
use Zoosper\Page\Admin\PageMomentumDefinitionProvider;

it('normalises disabled page momentum route and menu definitions', function (): void {
    $root = dirname(__DIR__, 5);
    $provider = new PageMomentumDefinitionProvider();

    $definitions = $provider->definitions(
        require $root . '/app/zoosper-page/config/admin_page_momentum_routes.php',
        require $root . '/app/zoosper-page/config/admin_page_momentum_menu.php'
    );

    expect($definitions['enabled'])->toBeFalse();
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

it('documents that runtime route and menu are still not live', function (): void {
    $root = dirname(__DIR__, 5);
    $doc = $root . '/docs/development/page-admin-momentum-phase-1.46-closure.md';

    expect($doc)->toBeFile();
    $contents = (string) file_get_contents($doc);
    expect($contents)->toContain('Runtime route is not registered');
    expect($contents)->toContain('Admin menu item is not enabled');
});
