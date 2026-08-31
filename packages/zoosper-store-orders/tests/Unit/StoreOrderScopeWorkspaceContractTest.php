<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests\Unit;

use InvalidArgumentException;
use Zoosper\StoreOrders\StoreOrderDataSourceFactory;
use Zoosper\StoreOrders\StoreOrderGrid;

it('declares request scope before approved result filters and bounded remote page sizes', function (): void {
    $definition = StoreOrderGrid::definition();
    expect(array_map(static fn ($filter) => $filter->key, $definition->grid->filters))->toBe([
        'store_code',
        'kiosk_website_id',
        'order_id',
        'customer',
        'status',
        'placed_from',
        'placed_to',
    ])->and($definition->pageSizes)->toBe([5, 10, 20, 50, 100]);
});

it('keeps request scope out of deployment settings', function (): void {
    $root = dirname(__DIR__, 4);
    $source = file_get_contents(
        $root . '/packages/zoosper-store-orders/config/settings/store_orders.php',
    );
    expect($source)->not->toBeFalse()
        ->and($source)->not->toContain('STORE_ORDERS_STORE_CODE')
        ->and($source)->not->toContain('STORE_ORDERS_KIOSK_WEBSITE_ID');
});

it('rejects invalid request scope before transport', function (): void {
    expect(fn () => (new StoreOrderDataSourceFactory())->create(
        ['api_base_url' => 'http://127.0.0.1:3000'],
        1,
        ['store_code' => 'bad', 'kiosk_website_id' => 55],
    ))->toThrow(InvalidArgumentException::class);
});











