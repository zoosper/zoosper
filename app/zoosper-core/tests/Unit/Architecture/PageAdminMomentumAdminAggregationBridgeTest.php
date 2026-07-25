<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;

it('keeps page momentum route and menu metadata available without aggregation bridge scaffolding', function (): void {
    $root = dirname(__DIR__, 5);
    $routeConfig = require $root . '/app/zoosper-page/config/admin_page_momentum_routes.php';
    $menuConfig = require $root . '/app/zoosper-page/config/admin_page_momentum_menu.php';

    expect($routeConfig)->toBeArray();
    expect($menuConfig)->toBeArray();
    expect(json_encode($routeConfig, JSON_THROW_ON_ERROR))->toContain('admin.page_momentum.index');
    expect(json_encode($menuConfig, JSON_THROW_ON_ERROR))->toContain('admin.page_momentum.index');
});

it('renders page momentum dashboard output from durable runtime files', function (): void {
    $html = (new PageMomentumAdminController())->index();

    expect($html)->toContain('Page momentum');
    expect($html)->toContain('/admin/page-momentum');
    expect($html)->toContain('read-only');
});
