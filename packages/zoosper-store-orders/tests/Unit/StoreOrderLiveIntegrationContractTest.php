<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests\Unit;

use InvalidArgumentException;
use Zoosper\StoreOrders\StoreOrderDataSourceFactory;

it('fails closed when request-carried Store Orders scope is invalid', function (): void {
    expect(fn () => (new StoreOrderDataSourceFactory())->create(
        ['api_base_url' => 'http://127.0.0.1:3000'],
        1,
        ['store_code' => 0, 'kiosk_website_id' => 55],
    ))->toThrow(InvalidArgumentException::class, 'Store code');
});

it('ships module-owned route menu ACL controller and settings configuration', function (): void {
    $root = dirname(__DIR__, 4);
    foreach ([
        'config/admin_routes.php',
        'config/admin_menu.php',
        'config/acl.php',
        'config/controllers.php',
        'config/settings/store_orders.php',
    ] as $path) {
        expect($root . '/packages/zoosper-store-orders/' . $path)->toBeFile();
    }

    $routes = require $root . '/packages/zoosper-store-orders/config/admin_routes.php';
    expect($routes[0]['path'] ?? null)->toBe('/admin/store-orders')
        ->and($routes[0]['permission'] ?? null)->toBe('store_order.view');
});
