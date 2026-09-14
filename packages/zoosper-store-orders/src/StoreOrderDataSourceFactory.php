<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders;

use InvalidArgumentException;
use Zoosper\ApiGrid\ApiGridDataSource;
use Zoosper\ApiGrid\Authentication\NoAuthentication;
use Zoosper\ApiGrid\Mapping\ApiGridContext;
use Zoosper\ApiGrid\Transport\ApiReliabilityPolicy;
use Zoosper\ApiGrid\Transport\CurlJsonApiTransport;
use Zoosper\StoreOrders\Api\StoreOrderRequestMapper;
use Zoosper\StoreOrders\Api\StoreOrderResponseMapper;

final class StoreOrderDataSourceFactory
{
    /**
     * @param array<string, mixed> $config
     */
    public function create(array $config, int $adminUserId): ApiGridDataSource
    {
        if (($config['enabled'] ?? false) !== true) {
            throw new InvalidArgumentException('Store Orders integration is not enabled.');
        }
        $baseUrl = trim((string) ($config['api_base_url'] ?? ''));
        if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Store Orders API base URL must be an absolute URL.');
        }
        $storeCode = self::positiveInteger($config['store_code'] ?? null, 'Store code');
        $websiteId = self::positiveInteger($config['kiosk_website_id'] ?? null, 'Kiosk website ID');

        return new ApiGridDataSource(
            new CurlJsonApiTransport($baseUrl),
            new StoreOrderRequestMapper(),
            new StoreOrderResponseMapper(),
            new NoAuthentication(),
            new ApiGridContext($adminUserId, scope: [
                'store_code' => $storeCode,
                'kiosk_website_id' => $websiteId,
            ]),
            StoreOrderCapabilities::currentApi(),
            new ApiReliabilityPolicy(
                connectTimeoutMilliseconds: (int) ($config['connect_timeout_ms'] ?? 1000),
                requestTimeoutMilliseconds: (int) ($config['request_timeout_ms'] ?? 5000),
                maximumResponseBytes: (int) ($config['maximum_response_bytes'] ?? 2000000),
            ),
        );
    }

    private static function positiveInteger(mixed $value, string $label): int
    {
        if (is_array($value) || !is_scalar($value) || !preg_match('/^[1-9][0-9]*$/', (string) $value)) {
            throw new InvalidArgumentException($label . ' must be a positive integer.');
        }
        $number = (int) $value;
        if ($number > 2147483647) {
            throw new InvalidArgumentException($label . ' is outside the supported range.');
        }
        return $number;
    }
}











