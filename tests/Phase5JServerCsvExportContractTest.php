<?php

declare(strict_types=1);

it('registers real server CSV routes for Pages and Store Orders', function (): void {
    $root = dirname(__DIR__);
    $pageRoutes = require $root . '/app/zoosper-page/config/admin_routes.php';
    $storeRoutes = require $root . '/packages/zoosper-store-orders/config/admin_routes.php';
    expect($pageRoutes)->toContain([
        'method' => 'GET', 'path' => '/admin/pages/export',
        'controller' => 'Zoosper\\Page\\Admin\\Controller\\PageCsvExportController',
        'action' => 'export', 'permission' => 'page.manage',
    ])->and($storeRoutes)->toContain([
        'method' => 'GET', 'path' => '/admin/store-orders/export',
        'controller' => Zoosper\StoreOrders\Admin\StoreOrderCsvExportController::class,
        'action' => 'export', 'permission' => 'store_order.export',
    ]);
});

it('returns CSV through Response raw with attachment headers', function (): void {
    $root = dirname(__DIR__);
    foreach ([
        $root . '/app/zoosper-page/src/Admin/Controller/PageCsvExportController.php',
        $root . '/packages/zoosper-store-orders/src/Admin/StoreOrderCsvExportController.php',
    ] as $path) {
        $source = file_get_contents($path);
        expect($source)->toContain('Response::raw')->toContain('->headers()')->toContain('\\xEF\\xBB\\xBF');
    }
});
