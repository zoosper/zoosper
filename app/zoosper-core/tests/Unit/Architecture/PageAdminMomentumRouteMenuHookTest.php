<?php

declare(strict_types=1);

use Zoosper\Core\Http\Response;
use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
use Zoosper\Page\Admin\Controller\PageMomentumAdminHttpController;

it('keeps page momentum route/menu metadata available without route-menu hook scaffolding', function (): void {
    $root = dirname(__DIR__, 5);
    $routeConfig = require $root . '/app/zoosper-page/config/admin_page_momentum_routes.php';
    $menuConfig = require $root . '/app/zoosper-page/config/admin_page_momentum_menu.php';

    expect(json_encode($routeConfig, JSON_THROW_ON_ERROR))->toContain('admin.page_momentum.index');
    expect(json_encode($menuConfig, JSON_THROW_ON_ERROR))->toContain('admin.page_momentum.index');
});

it('keeps the live HTTP controller response path available', function (): void {
    $response = (new PageMomentumAdminHttpController())->index();

    expect($response)->toBeInstanceOf(Response::class);
});

it('keeps dashboard page output available after route-menu hook removal', function (): void {
    $html = (new PageMomentumAdminController())->index();

    expect($html)->toContain('/admin/page-momentum');
    expect($html)->toContain('page.manage');
    expect($html)->toContain('read-only');
});
