<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests\Unit;

use InvalidArgumentException;
use Zoosper\StoreOrders\StoreOrderDataSourceFactory;
use Zoosper\StoreOrders\StoreOrderGrid;

it('declares only approved result filters and bounded remote page sizes', function (): void {
    $definition = StoreOrderGrid::definition();

    expect(array_map(static fn ($filter) => $filter->key, $definition->grid->filters))->toBe([
        'order_id',
        'customer',
        'status',
        'placed_from',
        'placed_to',
    ])->and($definition->pageSizes)->toBe([5, 10, 20, 50, 100]);
});

it('keeps trusted scope in deployment settings', function (): void {
    $source = file_get_contents(
        dirname(__DIR__, 4) . '/packages/zoosper-store-orders/config/settings/store_orders.php',
    );

    expect($source)->not->toBeFalse()
        ->and($source)->toContain(<<<'SOURCE'
'store_code' => $integration['store_code']
SOURCE)
        ->and($source)->toContain(<<<'SOURCE'
'kiosk_website_id' => $integration['kiosk_website_id']
SOURCE);
});

it('rejects disabled and invalid deployment scope before transport', function (): void {
    $factory = new StoreOrderDataSourceFactory();

    expect(fn () => $factory->create(['enabled' => false], 1))
        ->toThrow(InvalidArgumentException::class, 'not enabled');

    expect(fn () => $factory->create([
        'enabled' => true,
        'api_base_url' => 'http://127.0.0.1:3000',
        'store_code' => 'bad',
        'kiosk_website_id' => 55,
    ], 1))->toThrow(InvalidArgumentException::class);
});
