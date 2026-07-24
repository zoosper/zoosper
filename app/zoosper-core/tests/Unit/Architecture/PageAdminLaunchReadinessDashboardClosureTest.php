<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
use Zoosper\Page\Admin\PageAdminLaunchReadinessDashboardGuard;
use Zoosper\Page\Admin\PageAdminLaunchReadinessProvider;

it('passes launch readiness dashboard closure invariants', function (): void {
    $sections = (new PageAdminLaunchReadinessProvider())->sections();
    $html = (new PageMomentumAdminController())->index();
    $result = (new PageAdminLaunchReadinessDashboardGuard())->inspect($sections, $html);

    expect($result['ok'])->toBeTrue();
    expect($result['sectionCount'])->toBe(6);
    expect($result['missingHeadings'])->toBe([]);
});

it('keeps phase 1.58 closure tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/audit-page-admin-launch-readiness-dashboard-invariants.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-phase-158-closure.php')->toBeFile();
});
