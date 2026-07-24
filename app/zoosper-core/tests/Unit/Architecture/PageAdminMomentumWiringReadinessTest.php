<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;

it('can instantiate the page momentum controller and render a safe static panel', function (): void {
    $controller = new PageMomentumAdminController();
    $html = $controller->index();

    expect($html)->toContain('Page momentum');
    expect($html)->toContain('Core decoupling readiness');
    expect($html)->toContain('PageRenderer report-only candidate');
});

it('keeps route and menu metadata disabled until integration wiring', function (): void {
    $root = dirname(__DIR__, 5);
    $routeConfig = require $root . '/app/zoosper-page/config/admin_page_momentum_routes.php';
    $menuConfig = require $root . '/app/zoosper-page/config/admin_page_momentum_menu.php';

    expect($routeConfig['page_momentum_routes']['enabled'])->toBeFalse();
    expect($routeConfig['page_momentum_routes']['routes'][0]['controller'])->toBe(PageMomentumAdminController::class);
    expect($routeConfig['page_momentum_routes']['routes'][0]['action'])->toBe('index');
    expect($menuConfig['page_momentum_menu']['enabled'])->toBeFalse();
    expect($menuConfig['page_momentum_menu']['items'][0]['route'])->toBe('admin.page_momentum.index');
});

it('keeps page momentum controller proof tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/prove-page-admin-momentum-controller.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-wiring-readiness.php')->toBeFile();
});
