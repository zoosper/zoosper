<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
use Zoosper\Page\Admin\PageMomentumAdminDashboardShell;

it('wraps the page momentum dashboard in a standalone styled shell', function (): void {
    $html = (new PageMomentumAdminController())->index();

    expect($html)->toContain('<!doctype html>');
    expect($html)->toContain('<style>');
    expect($html)->toContain('zoosper-admin-shell');
    expect($html)->toContain('.zoosper-admin-card');
    expect($html)->toContain('.zoosper-admin-grid');
    expect($html)->toContain('Dashboard indicators');
    expect($html)->toContain('Page CRUD readiness');
    expect($html)->toContain('read-only');
});

it('keeps the durable visual shell smoke tool available', function (): void {
    $root = dirname(__DIR__, 5);

    expect(class_exists(PageMomentumAdminDashboardShell::class))->toBeTrue();
    expect($root . '/tools/smoke-page-admin-dashboard-visual-shell.php')->toBeFile();
});
