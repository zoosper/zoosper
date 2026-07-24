<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminRouteMenuHook;
use Zoosper\Page\Admin\PageMomentumAdminRouteMenuHookConsumerPreview;

it('previews the page momentum route/menu hook consumer patch without live mutation', function (): void {
    $root = dirname(__DIR__, 5);
    $runtimeConfig = require $root . '/app/zoosper-page/config/admin_page_momentum_runtime_aggregation_candidate.php';
    $hookCandidate = require $root . '/app/zoosper-page/config/admin_page_momentum_hook_candidate.php';

    $hookExport = (new PageMomentumAdminRouteMenuHook())->export($runtimeConfig, $hookCandidate);
    $preview = (new PageMomentumAdminRouteMenuHookConsumerPreview())->preview($hookExport, []);

    expect($preview['readyForConsumerPatch'])->toBeTrue();
    expect($preview['routeCount'])->toBe(1);
    expect($preview['menuCount'])->toBe(1);
    expect($preview['liveMutation'])->toBeFalse();
});

it('keeps phase 1.55 closure tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/prove-page-admin-momentum-route-menu-hook-consumer-preview.php')->toBeFile();
    expect($root . '/tools/generate-page-admin-momentum-route-menu-hook-source-plan.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-phase-155-closure.php')->toBeFile();
});
