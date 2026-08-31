<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests\Unit;

use InvalidArgumentException;
use Zoosper\ApiGrid\Mapping\ApiGridContext;
use Zoosper\Grid\DataSource\GridQuery;
use Zoosper\StoreOrders\Api\StoreOrderRequestMapper;
use Zoosper\StoreOrders\StoreOrderCapabilities;
use Zoosper\StoreOrders\StoreOrderGrid;

it('declares the approved remote order filters including date controls', function (): void {
    $definition = StoreOrderGrid::definition();
    $filters = [];
    foreach ($definition->grid->filters as $filter) {
        $filters[$filter->key] = $filter->type;
    }

    expect($filters)->toMatchArray([
        'store_code' => 'text',
        'kiosk_website_id' => 'text',
        'order_id' => 'text',
        'customer' => 'text',
        'status' => 'text',
        'placed_from' => 'date',
        'placed_to' => 'date',
    ])->and(StoreOrderCapabilities::currentApi()->filterableFields)
        ->toBe(['order_id', 'customer', 'status', 'placed_from', 'placed_to']);
});

it('maps only approved remote filters using the Node contract names', function (): void {
    $request = (new StoreOrderRequestMapper())->map(
        new GridQuery(filters: [
            'store_code' => 44,
            'kiosk_website_id' => 55,
            'order_id' => ' 145201973 ',
            'customer' => ' Sample Customer ',
            'status' => 'Charged',
            'placed_from' => '2026-07-01',
            'placed_to' => '2026-08-02',
            'private_payload' => 'must-not-leak',
        ]),
        new ApiGridContext(1, scope: ['store_code' => 44, 'kiosk_website_id' => 55]),
    );

    expect($request->query)->toMatchArray([
        'order_id' => '145201973',
        'customer' => 'Sample Customer',
        'status' => 'Charged',
        'placed_from' => '2026-07-01',
        'placed_to' => '2026-08-02',
    ])->not->toHaveKey('private_payload');
});

it('rejects invalid dates and reversed placed ranges before transport', function (): void {
    $mapper = new StoreOrderRequestMapper();
    $context = new ApiGridContext(1, scope: ['store_code' => 44, 'kiosk_website_id' => 55]);

    expect(fn () => $mapper->map(
        new GridQuery(filters: ['placed_from' => '02/08/2026']),
        $context,
    ))->toThrow(InvalidArgumentException::class, 'YYYY-MM-DD');

    expect(fn () => $mapper->map(
        new GridQuery(filters: ['placed_from' => '2026-08-03', 'placed_to' => '2026-08-02']),
        $context,
    ))->toThrow(InvalidArgumentException::class, 'Placed From');
});











