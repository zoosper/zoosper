<?php

declare(strict_types=1);

$env = static function (string $key, mixed $default = null): mixed {
    if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }

    $value = getenv($key);
    return $value !== false && $value !== '' ? $value : $default;
};

$decodeJsonMap = static function (string $value): array {
    if ($value === '') {
        return [];
    }

    $decoded = json_decode($value, true);
    if (!is_array($decoded)) {
        return [];
    }

    $map = [];
    foreach ($decoded as $key => $url) {
        if (is_string($key) && is_string($url) && $url !== '') {
            $map[$key] = $url;
        }
    }

    return $map;
};

return [
    /*
     * CDN URL configuration.
     *
     * Dynamic URLs are used for store-view aware links. Media URLs are used for
     * uploaded images/videos/files. Static URLs are used for static CSS/JS/JSON
     * and theme/module assets. These values must never contain credentials,
     * signed secrets, customer-private data, OTPs, TOTP secrets, recovery codes,
     * reset tokens or payment data.
     */
    'enabled' => filter_var($env('CDN_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

    'dynamic' => [
        'base_url' => (string) $env('CDN_DYNAMIC_BASE_URL', (string) $env('APP_URL', '')),
        'store_base_urls' => $decodeJsonMap((string) $env('CDN_DYNAMIC_STORE_BASE_URLS_JSON', '')),
    ],

    'media' => [
        'base_url' => (string) $env('CDN_MEDIA_BASE_URL', (string) $env('APP_URL', '')),
        'path_prefix' => (string) $env('CDN_MEDIA_PATH_PREFIX', '/media'),
    ],

    'static' => [
        'base_url' => (string) $env('CDN_STATIC_BASE_URL', (string) $env('APP_URL', '')),
        'path_prefix' => (string) $env('CDN_STATIC_PATH_PREFIX', '/static'),
    ],
];
