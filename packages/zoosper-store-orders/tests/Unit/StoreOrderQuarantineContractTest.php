<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests\Unit;

use InvalidArgumentException;
use Zoosper\StoreOrders\StoreOrderDataSourceFactory;
use Zoosper\StoreOrders\StoreOrderIntegrationGate;

function replaceStoreOrderEnvironment(array $values): void
{
    foreach (['STORE_ORDERS_ENABLED', 'STORE_ORDERS_API_BASE_URL', 'STORE_ORDERS_STORE_CODE', 'STORE_ORDERS_KIOSK_WEBSITE_ID'] as $key) {
        unset($_ENV[$key]);
        putenv($key);
    }
    foreach ($values as $key => $value) {
        $_ENV[$key] = (string) $value;
        putenv($key . '=' . $value);
    }
}

afterEach(fn () => replaceStoreOrderEnvironment([]));

it('is disabled and unconfigured by default', function (): void {
    replaceStoreOrderEnvironment([]);
    expect(StoreOrderIntegrationGate::configuration())->toMatchArray([
        'enabled' => false,
        'api_base_url' => '',
        'store_code' => null,
        'kiosk_website_id' => null,
    ]);
});

it('fails closed when enabled without complete trusted scope', function (): void {
    replaceStoreOrderEnvironment(['STORE_ORDERS_ENABLED' => 'true']);
    expect(fn () => StoreOrderIntegrationGate::configuration())
        ->toThrow(InvalidArgumentException::class);
});

it('accepts complete opt-in configuration', function (): void {
    replaceStoreOrderEnvironment([
        'STORE_ORDERS_ENABLED' => 'true',
        'STORE_ORDERS_API_BASE_URL' => 'http://127.0.0.1:3000',
        'STORE_ORDERS_STORE_CODE' => '3',
        'STORE_ORDERS_KIOSK_WEBSITE_ID' => '55',
    ]);
    expect(StoreOrderIntegrationGate::configuration())->toMatchArray([
        'enabled' => true,
        'store_code' => 3,
        'kiosk_website_id' => 55,
    ]);
});

it('refuses data-source construction while disabled', function (): void {
    expect(fn () => (new StoreOrderDataSourceFactory())->create([
        'enabled' => false,
        'api_base_url' => 'http://127.0.0.1:3000',
        'store_code' => 3,
        'kiosk_website_id' => 55,
    ], 1))->toThrow(InvalidArgumentException::class, 'not enabled');
});

it('keeps tenant scope out of query state and controller fallbacks', function (): void {
    $root = dirname(__DIR__, 4);
    $query = (string) file_get_contents($root . '/packages/zoosper-store-orders/src/Admin/StoreOrderGridQueryState.php');
    $listing = (string) file_get_contents($root . '/packages/zoosper-store-orders/src/Admin/StoreOrderAdminController.php');
    $export = (string) file_get_contents($root . '/packages/zoosper-store-orders/src/Admin/StoreOrderCsvExportController.php');

    expect($query)->not->toContain("'store_code'")->not->toContain("'kiosk_website_id'")
        ->and($listing)->not->toContain('??= 3')->not->toContain('??= 55')->not->toContain("filters['store_code']")
        ->and($export)->not->toContain('??= 3')->not->toContain('??= 55')->not->toContain("filters['store_code']");
});
