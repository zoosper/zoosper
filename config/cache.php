<?php

declare(strict_types=1);

$env = static function (string $key, mixed $default = null): mixed {
    if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }

    $value = getenv($key);
    return $value !== false && $value !== '' ? $value : $default;
};

return [
    'driver' => (string) $env('CACHE_DRIVER', 'file'),
    'path' => (string) $env('CACHE_PATH', 'var/cache/page'),
    'default_ttl' => (int) $env('CACHE_TTL', 3600),
    'redis' => [
        'host' => (string) $env('CACHE_REDIS_HOST', '127.0.0.1'),
        'port' => (int) $env('CACHE_REDIS_PORT', 6379),
        'password' => $env('CACHE_REDIS_PASSWORD'),
        'database' => (int) $env('CACHE_REDIS_DATABASE', 0),
        'prefix' => (string) $env('CACHE_REDIS_PREFIX', 'zoosper:cache:'),
    ],
];
