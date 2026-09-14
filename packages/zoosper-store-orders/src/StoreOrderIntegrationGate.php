<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders;

use InvalidArgumentException;

/**
 * Owns the fail-closed deployment boundary for the optional Store Orders integration.
 *
 * Disabled means the package contributes no runtime services, controllers, routes or
 * navigation. Enabling requires an absolute API URL and positive server-owned scope.
 */
final class StoreOrderIntegrationGate
{
    /** @return array{enabled: bool, api_base_url: string, store_code: int|null, kiosk_website_id: int|null} */
    public static function configuration(): array
    {
        $enabled = filter_var(self::value('STORE_ORDERS_ENABLED', false), FILTER_VALIDATE_BOOLEAN);
        $baseUrl = trim((string) self::value('STORE_ORDERS_API_BASE_URL', ''));
        $storeCode = self::optionalPositiveInteger(self::value('STORE_ORDERS_STORE_CODE'));
        $websiteId = self::optionalPositiveInteger(self::value('STORE_ORDERS_KIOSK_WEBSITE_ID'));

        if ($enabled) {
            if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
                throw new InvalidArgumentException(
                    'Store Orders is enabled but STORE_ORDERS_API_BASE_URL is not an absolute URL.',
                );
            }
            if ($storeCode === null || $websiteId === null) {
                throw new InvalidArgumentException(
                    'Store Orders is enabled but its positive store and kiosk website scope is incomplete.',
                );
            }
        }

        return [
            'enabled' => $enabled,
            'api_base_url' => $baseUrl,
            'store_code' => $storeCode,
            'kiosk_website_id' => $websiteId,
        ];
    }

    public static function enabled(): bool
    {
        return self::configuration()['enabled'];
    }

    private static function value(string $key, mixed $default = null): mixed
    {
        return function_exists('env') ? env($key, $default) : (getenv($key) !== false ? getenv($key) : $default);
    }

    private static function optionalPositiveInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_array($value) || !is_scalar($value) || preg_match('/^[1-9][0-9]*$/', (string) $value) !== 1) {
            return null;
        }
        $number = (int) $value;
        return $number <= 2147483647 ? $number : null;
    }

    private function __construct()
    {
    }
}
