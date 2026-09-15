<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests\Unit;

it('delegates Store Orders enablement endpoint and scope parsing to the integration gate', function (): void {
    $root = dirname(__DIR__, 4);
    $settings = (string) file_get_contents($root . '/packages/zoosper-store-orders/config/settings/store_orders.php');
    $gate = (string) file_get_contents($root . '/packages/zoosper-store-orders/src/StoreOrderIntegrationGate.php');
    expect($settings)->toContain('StoreOrderIntegrationGate::configuration()')
        ->and($settings)->toContain("env('STORE_ORDERS_CONNECT_TIMEOUT_MS'")
        ->and($settings)->toContain("env('STORE_ORDERS_REQUEST_TIMEOUT_MS'")
        ->and($settings)->toContain("env('STORE_ORDERS_MAXIMUM_RESPONSE_BYTES'")
        ->and($gate)->toContain("self::value('STORE_ORDERS_ENABLED'")
        ->and($gate)->toContain("self::value('STORE_ORDERS_API_BASE_URL'")
        ->and($gate)->toContain("self::value('STORE_ORDERS_API_TOKEN'")
        ->and($gate)->toContain("self::value('STORE_ORDERS_ALLOW_INSECURE_HTTP'")
        ->and($gate)->toContain("self::value('STORE_ORDERS_STORE_CODE'")
        ->and($gate)->toContain("self::value('STORE_ORDERS_KIOSK_WEBSITE_ID'");
});
