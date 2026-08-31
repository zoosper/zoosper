<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests\Unit;

it('uses the application env helper only for Store Orders transport configuration', function (): void {
    $root = dirname(__DIR__, 4);
    $source = file_get_contents(
        $root . '/packages/zoosper-store-orders/config/settings/store_orders.php',
    );

    expect($source)->not->toBeFalse()
        ->and($source)->toContain("env('STORE_ORDERS_API_BASE_URL'")
        ->and($source)->toContain("env('STORE_ORDERS_CONNECT_TIMEOUT_MS'")
        ->and($source)->toContain("env('STORE_ORDERS_REQUEST_TIMEOUT_MS'")
        ->and($source)->toContain("env('STORE_ORDERS_MAXIMUM_RESPONSE_BYTES'")
        ->and($source)->not->toContain("env('STORE_ORDERS_STORE_CODE'")
        ->and($source)->not->toContain("env('STORE_ORDERS_KIOSK_WEBSITE_ID'")
        ->and($source)->not->toContain('getenv(');
});











