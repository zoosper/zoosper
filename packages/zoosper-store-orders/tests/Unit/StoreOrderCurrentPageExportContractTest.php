<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests\Unit;

use Zoosper\StoreOrders\Admin\StoreOrderCsvExportController;
use Zoosper\StoreOrders\Tests\StoreOrdersTestEnvironment;

it('uses a real Store Orders server endpoint for current-page export when enabled', function (): void {
    StoreOrdersTestEnvironment::enabled(function (): void {
        $root = dirname(__DIR__, 4);
        $workspace = file_get_contents($root . '/packages/zoosper-store-orders/src/Admin/StoreOrderGridWorkspace.php');
        $routes = require $root . '/packages/zoosper-store-orders/config/admin_routes.php';
        expect($workspace)->not->toBeFalse()
            ->and($workspace)->toContain('$this->exportUrl()')
            ->and($workspace)->not->toContain('?grid_export=current')
            ->and($routes)->toContain([
                'method' => 'GET', 'path' => '/admin/store-orders/export',
                'controller' => StoreOrderCsvExportController::class,
                'action' => 'export', 'permission' => 'store_order.export',
            ]);

        $controller = (string) file_get_contents(
            $root . '/packages/zoosper-store-orders/src/Admin/StoreOrderCsvExportController.php',
        );
        expect($controller)
            ->toContain('$values = $request->queryParams()')
            ->toContain('filter_var($pageSizeValue, FILTER_VALIDATE_INT)')
            ->not->toContain('$_GET');
    });
});
