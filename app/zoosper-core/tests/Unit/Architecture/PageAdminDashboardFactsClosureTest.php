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

it('keeps durable dashboard facts closure classes available', function (): void {
    $root = dirname(__DIR__, 5);

    expect(PageAdminDashboardFactProvider::class)->toBeString();
    expect(PageAdminDashboardFactsGuard::class)->toBeString();
    expect($root . '/tools/audit-page-admin-dashboard-facts-closure.php')->toBeFile();
});
