<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminSourceHookAdapter;
use Zoosper\Page\Admin\PageMomentumAdminSourceHookPatchPreview;

it('builds a page momentum source hook patch preview without live mutation', function (): void {
    $root = dirname(__DIR__, 5);
    $hookCandidate = require $root . '/app/zoosper-page/config/admin_page_momentum_hook_candidate.php';
    $adapterExport = (new PageMomentumAdminSourceHookAdapter())->expose($hookCandidate);
    $preview = (new PageMomentumAdminSourceHookPatchPreview())->preview($adapterExport, []);

    expect($preview['readyForSourcePatch'])->toBeTrue();
    expect($preview['routeCount'])->toBe(1);
    expect($preview['menuCount'])->toBe(1);
    expect($preview['liveMutation'])->toBeFalse();
});

it('keeps phase 1.53 closure tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/prove-page-admin-momentum-source-hook-patch-preview.php')->toBeFile();
    expect($root . '/tools/generate-page-admin-momentum-source-hook-patch-preview.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-phase-153-closure.php')->toBeFile();
});
