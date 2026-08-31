<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests\Unit;

use UnexpectedValueException;
use Zoosper\ApiGrid\Mapping\ApiGridContext;
use Zoosper\ApiGrid\Transport\ApiResponse;
use Zoosper\Grid\DataSource\GridQuery;
use Zoosper\StoreOrders\Api\StoreOrderRequestMapper;
use Zoosper\StoreOrders\Api\StoreOrderResponseMapper;

it('maps trusted store scope and remote pagination to the Orders request', function (): void {
    $request = (new StoreOrderRequestMapper())->map(
        new GridQuery(page: 2, pageSize: 5),
        new ApiGridContext(7, scope: ['store_code' => 3, 'kiosk_website_id' => 55]),
    );

    expect($request->endpoint)->toBe('/v3/orders/store')
        ->and($request->query)->toBe([
            'page' => 2,
            'per_page' => 5,
            'store_code' => 3,
            'kiosk_website_id' => 55,
        ]);
});

it('normalises the Orders response without exposing nested personal payloads', function (): void {
    $response = new ApiResponse(200, [
        'records' => [[
            'order_entity_id' => 100,
            'order_id' => 'ORDER-100',
            'orderDate' => '2026-07-31T12:50:58.000Z',
            'status' => 'Charged',
            'payment_type' => 'Card',
            'totalPaid_fx' => 105.90,
            'customer_firstname' => 'Sample',
            'customer_lastname' => 'Customer',
            'customer_name' => 'Sample Customer',
            'order_data' => '{"shippingAddress":{"email":"private@example.test"}}',
            'picked_up_at' => null,
            'packed_at' => null,
            'tracking' => null,
            'TotalRows' => 126,
        ]],
        'total' => 126,
    ]);

    $result = (new StoreOrderResponseMapper())->map($response, new GridQuery(pageSize: 5));
    expect($result->total)->toBe(126)
        ->and($result->items[0])->toBe([
            'order_id' => 'ORDER-100',
            'order_date' => '2026-07-31T12:50:58+00:00',
            'customer_name' => 'Sample Customer',
            'status' => 'Charged',
            'payment_type' => 'Card',
            'total_paid' => 105.90,
            'picked_up_at' => null,
            'packed_at' => null,
            'tracking' => null,
        ])
        ->and($result->items[0])->not->toHaveKey('order_data')
        ->and($result->items[0])->not->toHaveKey('TotalRows');
});

it('rejects an invalid response envelope', function (): void {
    expect(fn () => (new StoreOrderResponseMapper())->map(
        new ApiResponse(200, ['records' => []]),
        new GridQuery(),
    ))->toThrow(UnexpectedValueException::class);
});











