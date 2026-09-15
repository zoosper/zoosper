<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests;

final class StoreOrdersTestEnvironment
{
    /** @param callable(): void $callback */
    public static function enabled(callable $callback): void
    {
        self::with([
            'STORE_ORDERS_ENABLED' => 'true',
            'STORE_ORDERS_API_BASE_URL' => 'http://127.0.0.1:3000',
            'STORE_ORDERS_API_TOKEN' => 'test-store-orders-token',
            'STORE_ORDERS_ALLOW_INSECURE_HTTP' => 'true',
            'STORE_ORDERS_STORE_CODE' => '3',
            'STORE_ORDERS_KIOSK_WEBSITE_ID' => '55',
        ], $callback);
    }

    /**
     * Runs one test with an explicit Store Orders environment and restores all values.
     *
     * @param array<string, scalar|null> $values
     * @param callable(): void $callback
     */
    public static function with(array $values, callable $callback): void
    {
        $keys = [
            'STORE_ORDERS_ENABLED',
            'STORE_ORDERS_API_BASE_URL',
            'STORE_ORDERS_API_TOKEN',
            'STORE_ORDERS_ALLOW_INSECURE_HTTP',
            'STORE_ORDERS_STORE_CODE',
            'STORE_ORDERS_KIOSK_WEBSITE_ID',
        ];
        $unknown = array_diff(array_keys($values), $keys);
        if ($unknown !== []) {
            throw new \InvalidArgumentException(
                'Unsupported Store Orders test environment key: ' . (string) reset($unknown),
            );
        }

        $previous = [];
        foreach ($keys as $key) {
            $value = getenv($key);
            $previous[$key] = $value === false ? null : $value;
            unset($_ENV[$key]);
            putenv($key);
        }

        try {
            foreach ($values as $key => $value) {
                if ($value !== null) {
                    self::set($key, (string) $value);
                }
            }
            $callback();
        } finally {
            foreach ($previous as $key => $value) {
                if ($value === null) {
                    unset($_ENV[$key]);
                    putenv($key);
                } else {
                    self::set($key, $value);
                }
            }
        }
    }

    private static function set(string $key, string $value): void
    {
        $_ENV[$key] = $value;
        putenv($key . '=' . $value);
    }

    private function __construct()
    {
    }
}
