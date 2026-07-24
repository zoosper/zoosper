<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumLiveCutoverPreflight;

it('passes page momentum live cutover preflight while metadata remains disabled', function (): void {
    $root = dirname(__DIR__, 5);
    $preflight = new PageMomentumLiveCutoverPreflight();

    $result = $preflight->inspect(
        require $root . '/app/zoosper-page/config/admin_page_momentum_routes.php',
        require $root . '/app/zoosper-page/config/admin_page_momentum_menu.php'
    );

    expect($result['readyForManualCutover'])->toBeTrue();
    expect($result['liveMutation'])->toBeFalse();
    expect($result['checks']['route_metadata_disabled'])->toBeTrue();
    expect($result['checks']['menu_metadata_disabled'])->toBeTrue();
});

it('keeps phase 1.48 preflight tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/audit-page-admin-momentum-live-cutover-preflight.php')->toBeFile();
    expect($root . '/tools/generate-page-admin-momentum-cutover-preview.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-phase-148-readiness.php')->toBeFile();
});
