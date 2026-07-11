<?php

declare(strict_types=1);

$env = static function (string $key, mixed $default = null): mixed {
    if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }

    $value = getenv($key);
    return $value !== false && $value !== '' ? $value : $default;
};

$decodeJson = static function (string $value): ?array {
    if ($value === '') {
        return null;
    }

    $decoded = json_decode($value, true);

    return is_array($decoded) ? $decoded : null;
};

$jsonConfig = $decodeJson((string) $env('SITE_CONTEXT_JSON', ''));
if ($jsonConfig !== null) {
    return $jsonConfig;
}

return [
    /*
     * Site/store/store-view context configuration.
     *
     * This is the lightweight foundation for Magento-inspired website, store and
     * store-view resolution. Domain/path matching should resolve the current
     * store view so feature code does not need to hard-code store codes.
     *
     * Do not store secrets here. Domains and base URLs must not contain OTPs,
     * TOTP secrets, recovery-code plaintext, reset tokens, payment data, SMTP
     * passwords, signed private URLs or customer-private values.
     */
    'default_store_view' => (string) $env('DEFAULT_STORE_VIEW_CODE', 'default'),

    'store_views' => [
        'default' => [
            'website_code' => (string) $env('DEFAULT_WEBSITE_CODE', 'main'),
            'website_name' => (string) $env('DEFAULT_WEBSITE_NAME', 'Main Website'),
            'store_code' => (string) $env('DEFAULT_STORE_CODE', 'main'),
            'store_name' => (string) $env('DEFAULT_STORE_NAME', 'Main Store'),
            'store_view_code' => (string) $env('DEFAULT_STORE_VIEW_CODE', 'default'),
            'store_view_name' => (string) $env('DEFAULT_STORE_VIEW_NAME', 'Default Store View'),
            'locale' => (string) $env('DEFAULT_LOCALE', 'en_AU'),
            'currency' => (string) $env('DEFAULT_CURRENCY', 'AUD'),
            'base_url' => (string) $env('APP_URL', 'http://localhost'),
            'domains' => array_values(array_filter(array_map('trim', explode(',', (string) $env('DEFAULT_STORE_VIEW_DOMAINS', ''))))),
            'path_prefix' => (string) $env('DEFAULT_STORE_VIEW_PATH_PREFIX', ''),
            'is_active' => true,
        ],
    ],
];
