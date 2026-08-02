<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests\Unit;

use Zoosper\StoreOrders\StoreOrderCapabilities;
use Zoosper\StoreOrders\StoreOrderGrid;

it('defines the first API Grid without advertising unsupported remote controls', function (): void {
    $definition = StoreOrderGrid::definition();
    $capabilities = StoreOrderCapabilities::currentApi();

    expect($definition->key)->toBe('store.orders')
        ->and($definition->route)->toBe('/admin/store-orders')
        ->and($definition->permission)->toBe('store_order.view')
        ->and($definition->exportPermission)->toBe('store_order.export')
        ->and($capabilities->searchable)->toBeFalse()
        ->and($capabilities->exportable)->toBeFalse()
        ->and($capabilities->sortableColumns)->toBe([])
        ->and($capabilities->filterableFields)->toBe([]);
});
