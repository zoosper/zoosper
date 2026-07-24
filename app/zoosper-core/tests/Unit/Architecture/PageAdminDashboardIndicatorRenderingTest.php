<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;

it('renders page admin dashboard indicators on the live momentum panel', function (): void {
    $html = (new PageMomentumAdminController())->index();

    expect($html)->toContain('Dashboard indicators');
    expect($html)->toContain('Page CRUD readiness');
    expect($html)->toContain('Preview/readiness status');
    expect($html)->toContain('Sidebar/menu health');
    expect($html)->toContain('Route/controller consistency');
    expect($html)->toContain('Media readiness');
    expect($html)->toContain('Documentation readiness');
    expect($html)->toContain('read-only');
});

it('keeps phase 1.60 indicator rendering tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/smoke-page-admin-dashboard-indicator-rendering.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-dashboard-indicator-rendering.php')->toBeFile();
});
