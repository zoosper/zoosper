<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminSourceHookAdapter;

it('exposes the page momentum hook candidate as route and menu arrays without live mutation', function (): void {
    $root = dirname(__DIR__, 5);
    $hookCandidate = require $root . '/app/zoosper-page/config/admin_page_momentum_hook_candidate.php';

    $exposed = (new PageMomentumAdminSourceHookAdapter())->expose($hookCandidate);

    expect($exposed['routeCount'])->toBe(1);
    expect($exposed['menuCount'])->toBe(1);
    expect($exposed['routes'][0]['name'])->toBe('admin.page_momentum.index');
    expect($exposed['menuItems'][0]['route'])->toBe('admin.page_momentum.index');
    expect($exposed['liveMutation'])->toBeFalse();
});

it('keeps phase 1.53 source hook tooling available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/prove-page-admin-momentum-source-hook-adapter.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-source-hook-readiness.php')->toBeFile();
});
