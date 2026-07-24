<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminAggregationBridge;
use Zoosper\Page\Admin\PageMomentumAdminConsumerHookPreview;

it('previews the consumer hook for page momentum admin aggregation without live mutation', function (): void {
    $root = dirname(__DIR__, 5);
    $candidate = require $root . '/app/zoosper-page/config/admin_page_momentum_runtime_candidate.php';

    $bridgeExport = (new PageMomentumAdminAggregationBridge())->export($candidate);
    $preview = (new PageMomentumAdminConsumerHookPreview())->preview($bridgeExport);

    expect($preview['readyForLiveHook'])->toBeTrue();
    expect($preview['routeCount'])->toBe(1);
    expect($preview['menuCount'])->toBe(1);
    expect($preview['liveMutation'])->toBeFalse();
});

it('keeps phase 1.51 closure tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/prove-page-admin-momentum-consumer-hook-preview.php')->toBeFile();
    expect($root . '/tools/generate-page-admin-momentum-consumer-hook-plan.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-phase-151-closure.php')->toBeFile();
});
