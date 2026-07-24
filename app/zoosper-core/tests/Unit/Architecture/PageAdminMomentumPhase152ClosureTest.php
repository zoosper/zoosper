<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminHookConsumerPreview;

it('previews the page momentum source hook candidate without live mutation', function (): void {
    $root = dirname(__DIR__, 5);
    $hookCandidate = require $root . '/app/zoosper-page/config/admin_page_momentum_hook_candidate.php';

    $preview = (new PageMomentumAdminHookConsumerPreview())->preview($hookCandidate);

    expect($preview['readyForSourceHook'])->toBeTrue();
    expect($preview['routeCount'])->toBe(1);
    expect($preview['menuCount'])->toBe(1);
    expect($preview['liveMutation'])->toBeFalse();
});

it('keeps phase 1.52 closure tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/prove-page-admin-momentum-hook-consumer-preview.php')->toBeFile();
    expect($root . '/tools/generate-page-admin-momentum-source-hook-plan.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-phase-152-closure.php')->toBeFile();
});
