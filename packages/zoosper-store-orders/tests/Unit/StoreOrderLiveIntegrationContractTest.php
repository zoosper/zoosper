<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests\Unit;

use Zoosper\StoreOrders\Admin\StoreOrderCsvExportController;
use Zoosper\StoreOrders\Tests\StoreOrdersTestEnvironment;

it('contributes no Store Orders runtime surface while disabled', function (): void {
    $root = dirname(__DIR__, 4);
    expect(require $root . '/packages/zoosper-store-orders/config/admin_routes.php')->toBe([])
        ->and(require $root . '/packages/zoosper-store-orders/config/admin_menu.php')->toBe([])
        ->and(require $root . '/packages/zoosper-store-orders/config/controllers.php')->toBe([])
        ->and(require $root . '/packages/zoosper-store-orders/config/services.php')->toBe([]);
});

it('ships module-owned routes and export permission when explicitly enabled', function (): void {
    StoreOrdersTestEnvironment::enabled(function (): void {
        $root = dirname(__DIR__, 4);
        $routes = require $root . '/packages/zoosper-store-orders/config/admin_routes.php';
        expect($routes[0]['path'] ?? null)->toBe('/admin/store-orders')
            ->and($routes[0]['permission'] ?? null)->toBe('store_order.view')
            ->and($routes)->toContain([
                'method' => 'GET', 'path' => '/admin/store-orders/export',
                'controller' => StoreOrderCsvExportController::class,
                'action' => 'export', 'permission' => 'store_order.export',
            ]);
    });
});
