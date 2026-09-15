<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests\Unit;

use Zoosper\Core\Config\ModuleConfigAggregator;
use Zoosper\Core\Module\ModuleRegistry;

it('publishes disabled Store Orders defaults and deployment-owned scope keys', function (): void {
    $root = dirname(__DIR__, 4);
    require_once $root . '/bootstrap/autoload.php';
    $aggregated = (new ModuleConfigAggregator(new ModuleRegistry($root), $root . '/config'))->aggregate();
    expect($aggregated)->toHaveKey('store_orders')
        ->and($aggregated['store_orders'])->toHaveKeys([
            'enabled', 'api_base_url', 'api_token', 'allow_insecure_http', 'store_code', 'kiosk_website_id',
            'connect_timeout_ms', 'request_timeout_ms', 'maximum_response_bytes',
        ])
        ->and($aggregated['store_orders']['enabled'])->toBeFalse()
        ->and($aggregated['store_orders']['store_code'])->toBeNull()
        ->and($aggregated['store_orders']['kiosk_website_id'])->toBeNull()
        ->and($aggregated['store_orders']['connect_timeout_ms'])->toBeInt()->toBeGreaterThan(0)
        ->and($aggregated['store_orders']['request_timeout_ms'])->toBeInt()->toBeGreaterThan(0)
        ->and($aggregated['store_orders']['maximum_response_bytes'])->toBeInt()->toBeGreaterThan(0);
});
