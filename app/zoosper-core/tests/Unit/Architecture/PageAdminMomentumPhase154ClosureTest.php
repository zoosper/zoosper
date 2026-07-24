<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminRuntimeAggregationProvider;
use Zoosper\Page\Admin\PageMomentumAdminRuntimeHookPreview;

it('previews the page momentum runtime source hook without live mutation', function (): void {
    $root = dirname(__DIR__, 5);
    $config = require $root . '/app/zoosper-page/config/admin_page_momentum_runtime_aggregation_candidate.php';
    $hookCandidate = require $root . '/app/zoosper-page/config/admin_page_momentum_hook_candidate.php';

    $payload = (new PageMomentumAdminRuntimeAggregationProvider())->provide($config, $hookCandidate);
    $preview = (new PageMomentumAdminRuntimeHookPreview())->preview($payload);

    expect($preview['readyForRuntimeSourceHook'])->toBeTrue();
    expect($preview['routeCount'])->toBe(1);
    expect($preview['menuCount'])->toBe(1);
    expect($preview['liveMutation'])->toBeFalse();
});

it('keeps phase 1.54 closure tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/prove-page-admin-momentum-runtime-hook-preview.php')->toBeFile();
    expect($root . '/tools/generate-page-admin-momentum-runtime-source-hook-plan.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-phase-154-closure.php')->toBeFile();
});
