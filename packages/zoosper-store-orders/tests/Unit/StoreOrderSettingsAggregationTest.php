<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests\Unit;

use Zoosper\Core\Config\ModuleConfigAggregator;
use Zoosper\Core\Module\ModuleRegistry;

it('publishes Store Orders transport defaults through module settings aggregation', function (): void {
    $root = dirname(__DIR__, 4);
    require_once $root . '/bootstrap/autoload.php';

    $aggregated = (new ModuleConfigAggregator(
        new ModuleRegistry($root),
        $root . '/config',
    ))->aggregate();

    expect($root . '/packages/zoosper-store-orders/config/store_orders.php')->not->toBeFile()
        ->and($root . '/packages/zoosper-store-orders/config/settings/store_orders.php')->toBeFile()
        ->and($aggregated)->toHaveKey('store_orders')
        ->and($aggregated['store_orders'])->toHaveKeys([
            'api_base_url',
            'connect_timeout_ms',
            'request_timeout_ms',
            'maximum_response_bytes',
        ])
        ->and($aggregated['store_orders'])->not->toHaveKey('store_code')
        ->and($aggregated['store_orders'])->not->toHaveKey('kiosk_website_id');

    expect($aggregated['store_orders']['api_base_url'])->toBeString()->not->toBeEmpty()
        ->and($aggregated['store_orders']['connect_timeout_ms'])->toBeInt()->toBeGreaterThan(0)
        ->and($aggregated['store_orders']['request_timeout_ms'])->toBeInt()->toBeGreaterThan(0)
        ->and($aggregated['store_orders']['maximum_response_bytes'])->toBeInt()->toBeGreaterThan(0);
});











