<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumActivationGuard;
use Zoosper\Page\Admin\PageMomentumAdminIntegrationPreview;

it('activates page momentum metadata consistently', function (): void {
    $root = dirname(__DIR__, 5);
    $guard = new PageMomentumActivationGuard();

    $result = $guard->inspect(
        require $root . '/app/zoosper-page/config/admin_page_momentum.php',
        require $root . '/app/zoosper-page/config/admin_page_momentum_routes.php',
        require $root . '/app/zoosper-page/config/admin_page_momentum_menu.php'
    );

    expect($result['ready'])->toBeTrue();
    expect($result['checks']['momentum_enabled'])->toBeTrue();
    expect($result['checks']['route_metadata_enabled'])->toBeTrue();
    expect($result['checks']['menu_metadata_enabled'])->toBeTrue();
});

it('exports one route and one menu definition after activation', function (): void {
    $root = dirname(__DIR__, 5);
    $preview = (new PageMomentumAdminIntegrationPreview())->preview(
        require $root . '/app/zoosper-page/config/admin_page_momentum_routes.php',
        require $root . '/app/zoosper-page/config/admin_page_momentum_menu.php'
    );

    expect($preview['routeCount'])->toBe(1);
    expect($preview['menuCount'])->toBe(1);
    expect($preview['liveMutation'])->toBeFalse();
});

it('keeps phase 1.48 closure tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/prove-page-admin-momentum-metadata-activation.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-live-smoke.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-phase-148-closure.php')->toBeFile();
});
