<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests\Unit;

use Zoosper\StoreOrders\Admin\StoreOrderCsvExportController;

it('ships module-owned route menu ACL controller and settings configuration', function (): void {
    $root = dirname(__DIR__, 4);
    foreach (['config/admin_routes.php','config/admin_menu.php','config/acl.php','config/controllers.php','config/settings/store_orders.php'] as $path) {
        expect($root . '/packages/zoosper-store-orders/' . $path)->toBeFile();
    }
    $routes = require $root . '/packages/zoosper-store-orders/config/admin_routes.php';
    expect($routes[0]['path'] ?? null)->toBe('/admin/store-orders')
        ->and($routes[0]['permission'] ?? null)->toBe('store_order.view')
        ->and($routes)->toContain([
            'method' => 'GET', 'path' => '/admin/store-orders/export',
            'controller' => StoreOrderCsvExportController::class,
            'action' => 'export', 'permission' => 'store_order.export',
        ]);
});











