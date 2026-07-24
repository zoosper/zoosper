<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminLiveAggregationIntegrator;

it('merges the page momentum route into keyed route config once', function (): void {
    $integrator = new PageMomentumAdminLiveAggregationIntegrator();
    $route = [
        'name' => 'admin.page_momentum.index',
        'method' => 'GET',
        'path' => '/admin/page-momentum',
        'permission' => 'page.manage',
    ];

    $config = $integrator->mergeRoutes(['routes' => []], [$route]);
    $config = $integrator->mergeRoutes($config, [$route]);

    expect($config['routes'])->toHaveCount(1);
    expect($config['routes'][0]['name'])->toBe('admin.page_momentum.index');
});

it('merges the page momentum menu item into keyed menu config once', function (): void {
    $integrator = new PageMomentumAdminLiveAggregationIntegrator();
    $item = [
        'label' => 'Page momentum',
        'route' => 'admin.page_momentum.index',
        'permission' => 'page.manage',
    ];

    $config = $integrator->mergeMenu(['items' => []], [$item]);
    $config = $integrator->mergeMenu($config, [$item]);

    expect($config['items'])->toHaveCount(1);
    expect($config['items'][0]['route'])->toBe('admin.page_momentum.index');
});

it('keeps phase 1.56 live aggregation tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/apply-page-admin-momentum-live-aggregation.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-live-aggregation.php')->toBeFile();
    expect($root . '/tools/smoke-page-admin-momentum-live-files.php')->toBeFile();
});
