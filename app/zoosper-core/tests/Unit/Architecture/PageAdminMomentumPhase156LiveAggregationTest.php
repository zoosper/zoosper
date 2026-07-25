<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;

it('keeps the live page momentum route metadata present after consolidation', function (): void {
    $root = dirname(__DIR__, 5);
    $routeConfig = require $root . '/app/zoosper-page/config/admin_page_momentum_routes.php';
    $route = $routeConfig['page_momentum_routes']['routes'][0] ?? [];

    expect($route)->toBeArray();
    expect($route['name'] ?? null)->toBe('admin.page_momentum.index');
    expect($route['path'] ?? null)->toBe('/admin/page-momentum');
    expect($route['permission'] ?? null)->toBe('page.manage');
});

it('keeps the live page momentum menu metadata present after consolidation', function (): void {
    $root = dirname(__DIR__, 5);
    $menuConfig = require $root . '/app/zoosper-page/config/admin_page_momentum_menu.php';
    $encoded = json_encode($menuConfig, JSON_THROW_ON_ERROR);

    expect($encoded)->toContain('Page momentum');
    expect($encoded)->toContain('admin.page_momentum.index');
    expect($encoded)->toContain('page.manage');
});

it('keeps the dashboard rendering after live aggregation scaffolding removal', function (): void {
    $html = (new PageMomentumAdminController())->index();

    expect($html)->toContain('Live status');
    expect($html)->toContain('Page Admin launch-readiness dashboard');
    expect($html)->toContain('Dashboard indicators');
});
