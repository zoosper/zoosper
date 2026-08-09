<?php

declare(strict_types=1);

it('retires the internal Page Momentum dashboard from the production Admin surface', function (): void {
    $root = dirname(__DIR__, 5);
    $routes = (string) file_get_contents($root . '/app/zoosper-page/config/admin_routes.php');

    expect($routes)
        ->not->toContain('/admin/page-momentum')
        ->not->toContain('admin.page_momentum.index')
        ->not->toContain('PageMomentumAdminHttpController');

    expect($root . '/app/zoosper-page/config/admin_page_momentum.php')->not->toBeFile()
        ->and($root . '/app/zoosper-page/config/admin_page_momentum_menu.php')->not->toBeFile()
        ->and($root . '/app/zoosper-page/config/admin_page_momentum_routes.php')->not->toBeFile()
        ->and($root . '/app/zoosper-page/resources/views/admin/page-momentum.latte')->not->toBeFile()
        ->and($root . '/app/zoosper-page/src/Admin/Controller/PageMomentumAdminController.php')->not->toBeFile()
        ->and($root . '/app/zoosper-page/src/Admin/PageMomentumStatusProvider.php')->not->toBeFile();
});

it('keeps release readiness on durable automated gates instead of an Admin status page', function (): void {
    $root = dirname(__DIR__, 5);
    $composer = json_decode(
        (string) file_get_contents($root . '/composer.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );

    expect($composer['scripts'])->toHaveKeys([
        'test',
        'compile',
        'gate:strict',
        'fresh-install:smoke',
        'release:check',
    ]);
});
