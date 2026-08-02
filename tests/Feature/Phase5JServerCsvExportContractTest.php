<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageCsvExportController;
use Zoosper\StoreOrders\Admin\StoreOrderCsvExportController;

it('registers real server CSV routes for Pages and Store Orders', function (): void {
    $root = dirname(__DIR__, 2);
    $pageRoutes = require $root . '/app/zoosper-page/config/admin_routes.php';
    $storeRoutes = require $root . '/packages/zoosper-store-orders/config/admin_routes.php';

    expect($pageRoutes)->toContain([
        'method' => 'GET', 'path' => '/admin/pages/export',
        'controller' => PageCsvExportController::class,
        'action' => 'export', 'permission' => 'page.manage',
    ])->and($storeRoutes)->toContain([
        'method' => 'GET', 'path' => '/admin/store-orders/export',
        'controller' => StoreOrderCsvExportController::class,
        'action' => 'export', 'permission' => 'store_order.export',
    ]);
});

it('returns CSV through Response raw with attachment headers', function (): void {
    $root = dirname(__DIR__, 2);
    foreach ([
        $root . '/app/zoosper-page/src/Admin/Controller/PageCsvExportController.php',
        $root . '/packages/zoosper-store-orders/src/Admin/StoreOrderCsvExportController.php',
    ] as $path) {
        $source = file_get_contents($path);
        expect($source)->not->toBeFalse()
            ->and($source)->toContain('Response::raw')
            ->and($source)->toContain('->headers()')
            ->and($source)->toContain('\\xEF\\xBB\\xBF');
    }
});
