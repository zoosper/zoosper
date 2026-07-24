<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminAggregationBridge;
use Zoosper\Page\Admin\PageMomentumAdminHookProvider;

it('builds a stable page momentum admin hook payload without live mutation', function (): void {
    $root = dirname(__DIR__, 5);
    $candidate = require $root . '/app/zoosper-page/config/admin_page_momentum_runtime_candidate.php';
    $bridgeExport = (new PageMomentumAdminAggregationBridge())->export($candidate);
    $payload = (new PageMomentumAdminHookProvider())->payload($bridgeExport);

    $hook = $payload['page_momentum_admin_hook'];

    expect($hook['enabled'])->toBeTrue();
    expect($hook['routes'])->toBeArray()->toHaveCount(1);
    expect($hook['menu_items'])->toBeArray()->toHaveCount(1);
    expect($hook['live_mutation'])->toBeFalse();
});

it('keeps phase 1.52 hook tooling available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/generate-page-admin-momentum-hook-candidate.php')->toBeFile();
    expect($root . '/tools/prove-page-admin-momentum-hook-provider.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-hook-readiness.php')->toBeFile();
});
