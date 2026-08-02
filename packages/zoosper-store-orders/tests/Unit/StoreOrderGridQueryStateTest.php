<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests\Unit;

use Zoosper\StoreOrders\Admin\StoreOrderGridQueryState;

it('nests flat Store Orders HTTP filters for the shared workspace resolver', function (): void {
    $state = StoreOrderGridQueryState::fromQuery([
        'store_code' => '49',
        'kiosk_website_id' => '55',
        'order_id' => '145201973',
        'customer' => 'Sample Customer',
        'status' => 'Charged',
        'placed_from' => '2026-07-01',
        'placed_to' => '2026-08-02',
        'page' => '2',
        'page_size' => '50',
        'sort' => 'order_date',
        'dir' => 'desc',
        'unexpected' => 'must-not-enter-filter-state',
    ]);

    expect($state['filters'])->toBe([
        'store_code' => '49',
        'kiosk_website_id' => '55',
        'order_id' => '145201973',
        'customer' => 'Sample Customer',
        'status' => 'Charged',
        'placed_from' => '2026-07-01',
        'placed_to' => '2026-08-02',
    ])->and($state['page'])->toBe(2)
        ->and($state['page_size'])->toBe(50)
        ->and($state['sort_by'])->toBe('order_date')
        ->and($state['sort_dir'])->toBe('desc')
        ->and($state['filters'])->not->toHaveKey('unexpected');
});

it('preserves explicit column workspace state and resolves bookmark identifiers', function (): void {
    $state = StoreOrderGridQueryState::fromQuery([
        'visible_columns' => ['order_id', 'status'],
        'column_order' => ['status', 'order_id'],
    ]);

    expect($state['visible_columns'])->toBe(['order_id', 'status'])
        ->and($state['column_order'])->toBe(['status', 'order_id'])
        ->and(StoreOrderGridQueryState::bookmarkId(['bookmark_id' => '7']))->toBe(7)
        ->and(StoreOrderGridQueryState::bookmarkId([]))->toBeNull();
});

it('makes the controller pass normalised query state into the workspace', function (): void {
    $root = dirname(__DIR__, 4);
    $source = file_get_contents(
        $root . '/packages/zoosper-store-orders/src/Admin/StoreOrderAdminController.php',
    );

    expect($source)->not->toBeFalse()
        ->and($source)->toContain('StoreOrderGridQueryState::fromQuery($values)')
        ->and($source)->toContain('queryState: $queryState')
        ->and($source)->not->toContain('queryState: $values');
});
