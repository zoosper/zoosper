<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
use Zoosper\Page\Admin\PageAdminDashboardStatusSystemGuard;

it('passes page admin dashboard status system closure invariants', function (): void {
    $html = (new PageMomentumAdminController())->index();
    $result = (new PageAdminDashboardStatusSystemGuard())->inspect($html);

    expect($result['ok'])->toBeTrue();
    expect($result['missingTokens'])->toBe([]);
    expect($result['statusTokenCount'])->toBe(7);
});

it('keeps phase 1.61 closure tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/audit-page-admin-dashboard-status-system-closure.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-phase-161-closure.php')->toBeFile();
});
