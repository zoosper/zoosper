<?php

declare(strict_types=1);

it('keeps phase 1.45 route and menu momentum metadata available but disabled', function (): void {
    $root = dirname(__DIR__, 5);

    $routeConfig = require $root . '/app/zoosper-page/config/admin_page_momentum_routes.php';
    $menuConfig = require $root . '/app/zoosper-page/config/admin_page_momentum_menu.php';

    expect($routeConfig['page_momentum_routes']['enabled'])->toBeFalse();
    expect($routeConfig['page_momentum_routes']['routes'])->toBeArray();
    expect($menuConfig['page_momentum_menu']['enabled'])->toBeFalse();
    expect($menuConfig['page_momentum_menu']['items'])->toBeArray();
});

it('keeps phase 1.45 closure tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/audit-page-admin-route-menu-conventions.php')->toBeFile();
    expect($root . '/tools/write-page-admin-momentum-wiring-plan.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-phase-145-closure.php')->toBeFile();
});

it('documents that route and menu wiring has not happened yet', function (): void {
    $root = dirname(__DIR__, 5);
    $doc = $root . '/docs/development/page-admin-momentum-phase-1.45-closure.md';

    expect($doc)->toBeFile();
    $contents = (string) file_get_contents($doc);
    expect($contents)->toContain('Runtime route is not changed');
    expect($contents)->toContain('Admin menu is not changed');
});
