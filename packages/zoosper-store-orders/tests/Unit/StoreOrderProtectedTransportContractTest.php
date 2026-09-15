<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests\Unit;

use InvalidArgumentException;
use Zoosper\ApiGrid\Authentication\BearerTokenAuthentication;
use Zoosper\StoreOrders\StoreOrderDataSourceFactory;
use Zoosper\StoreOrders\StoreOrderIntegrationGate;
use Zoosper\StoreOrders\Tests\StoreOrdersTestEnvironment;

it('requires https for non-loopback Store Orders endpoints', function (): void {
    replaceStoreOrderEnvironment([
        'STORE_ORDERS_ENABLED' => 'true',
        'STORE_ORDERS_API_BASE_URL' => 'http://orders.example.test',
        'STORE_ORDERS_API_TOKEN' => 'safe-token',
        'STORE_ORDERS_STORE_CODE' => '3',
        'STORE_ORDERS_KIOSK_WEBSITE_ID' => '55',
    ]);
    expect(fn () => StoreOrderIntegrationGate::configuration())->toThrow(InvalidArgumentException::class, 'requires HTTPS');
});

it('allows explicitly opted-in loopback http for local integration tests', function (): void {
    StoreOrdersTestEnvironment::enabled(function (): void {
        expect(StoreOrderIntegrationGate::configuration()['allow_insecure_http'])->toBeTrue();
    });
});

it('requires a header-safe Store Orders token', function (string $token): void {
    replaceStoreOrderEnvironment([
        'STORE_ORDERS_ENABLED' => 'true',
        'STORE_ORDERS_API_BASE_URL' => 'https://orders.example.test',
        'STORE_ORDERS_API_TOKEN' => $token,
        'STORE_ORDERS_STORE_CODE' => '3',
        'STORE_ORDERS_KIOSK_WEBSITE_ID' => '55',
    ]);
    expect(fn () => StoreOrderIntegrationGate::configuration())->toThrow(InvalidArgumentException::class, 'API_TOKEN');
})->with(['', "unsafe\r\nheader"]);

it('uses the generic bearer authenticator instead of unauthenticated transport', function (): void {
    $source = (string) file_get_contents(dirname(__DIR__, 4) . '/packages/zoosper-store-orders/src/StoreOrderDataSourceFactory.php');
    expect($source)->toContain(BearerTokenAuthentication::class)
        ->and($source)->not->toContain('NoAuthentication');
});

it('keeps secrets out of URLs and stable transport exception messages', function (): void {
    $factory = (string) file_get_contents(dirname(__DIR__, 4) . '/packages/zoosper-store-orders/src/StoreOrderDataSourceFactory.php');
    $transport = (string) file_get_contents(dirname(__DIR__, 4) . '/packages/zoosper-api-grid/src/Transport/CurlJsonApiTransport.php');
    expect($factory)->not->toContain("'api_token' =>")
        ->and($transport)->not->toContain('curl_error(')
        ->and($transport)->not->toContain('CURLINFO_EFFECTIVE_URL')
        ->and($transport)->toContain('CURLOPT_FOLLOWLOCATION => false');
});
