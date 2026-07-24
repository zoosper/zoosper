<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageAdminDashboardIndicatorProvider;
use Zoosper\Page\Admin\PageAdminLaunchReadinessProvider;

it('provides richer page admin dashboard indicators', function (): void {
    $indicators = (new PageAdminDashboardIndicatorProvider())->indicators();

    expect($indicators)->toHaveCount(6);
    expect(array_column($indicators, 'label'))->toContain('Page CRUD readiness');
    expect(array_column($indicators, 'label'))->toContain('Preview/readiness status');
    expect(array_column($indicators, 'label'))->toContain('Sidebar/menu health');
    expect(array_column($indicators, 'label'))->toContain('Route/controller consistency');
});

it('keeps launch readiness sections stable while referencing richer indicators', function (): void {
    $sections = (new PageAdminLaunchReadinessProvider())->sections();

    expect($sections)->toHaveCount(6);
    expect($sections[4]['heading'])->toBe('Admin UX readiness');
    expect($sections[4]['detail'])->toContain('richer indicators');
});

it('keeps phase 1.59 indicator tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/smoke-page-admin-dashboard-indicators.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-dashboard-indicators.php')->toBeFile();
});
