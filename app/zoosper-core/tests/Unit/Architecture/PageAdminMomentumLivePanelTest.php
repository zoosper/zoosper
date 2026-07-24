<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
use Zoosper\Page\Admin\PageMomentumStatusProvider;

it('renders the live page momentum panel with route, permission and read-only state', function (): void {
    $html = (new PageMomentumAdminController())->index();

    expect($html)->toContain('Page momentum');
    expect($html)->toContain('/admin/page-momentum');
    expect($html)->toContain('page.manage');
    expect($html)->toContain('read-only');
    expect($html)->toContain('Rollback');
});

it('provides live page momentum status items', function (): void {
    $items = (new PageMomentumStatusProvider())->items();

    expect($items)->toBeArray()->toHaveCount(4);
    expect($items[0]['label'])->toBe('Live admin route');
});

it('keeps phase 1.57 live panel tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/smoke-page-admin-momentum-live-panel.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-phase-157-readiness.php')->toBeFile();
});
