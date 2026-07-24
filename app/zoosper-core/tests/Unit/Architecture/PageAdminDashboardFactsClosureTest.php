<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
use Zoosper\Page\Admin\PageAdminDashboardFactProvider;
use Zoosper\Page\Admin\PageAdminDashboardFactsGuard;

it('passes page admin dashboard facts closure invariants', function (): void {
    $facts = (new PageAdminDashboardFactProvider())->facts();
    $html = (new PageMomentumAdminController())->index();
    $result = (new PageAdminDashboardFactsGuard())->inspect($facts, $html);

    expect($result['ok'])->toBeTrue();
    expect($result['factCount'])->toBe(4);
    expect($result['missingLabels'])->toBe([]);
    expect($result['unknownStatuses'])->toBe([]);
});

it('keeps phase 1.62 closure tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/audit-page-admin-dashboard-facts-closure.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-phase-162-closure.php')->toBeFile();
});
