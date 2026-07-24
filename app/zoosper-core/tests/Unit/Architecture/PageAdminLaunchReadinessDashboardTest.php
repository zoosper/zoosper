<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
use Zoosper\Page\Admin\PageAdminLaunchReadinessProvider;

it('renders the page admin launch readiness dashboard from the live momentum panel', function (): void {
    $html = (new PageMomentumAdminController())->index();

    expect($html)->toContain('Page Admin launch-readiness dashboard');
    expect($html)->toContain('/admin/page-momentum');
    expect($html)->toContain('page.manage');
    expect($html)->toContain('PageRenderer report-only candidate');
    expect($html)->toContain('Core decoupling readiness');
    expect($html)->toContain('read-only');
});

it('provides launch readiness sections', function (): void {
    $sections = (new PageAdminLaunchReadinessProvider())->sections();

    expect($sections)->toBeArray()->toHaveCount(6);
    expect($sections[0]['heading'])->toBe('Live route and menu');
});

it('keeps phase 1.58 dashboard tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/smoke-page-admin-launch-readiness-dashboard.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-launch-readiness-dashboard.php')->toBeFile();
});
