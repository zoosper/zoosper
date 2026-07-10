<?php

declare(strict_types=1);

$env = static function (string $key, mixed $default = null): mixed {
    if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }

    $value = getenv($key);
    return $value !== false && $value !== '' ? $value : $default;
};

$basePath = '/' . trim((string) $env('ADMIN_BASE_PATH', '/admin'), '/');

return [
    /*
     * Admin base path.
     *
     * New admin controllers should avoid hard-coding /admin and should build
     * internal admin URLs from this value instead. Route declarations still use
     * current route-loader paths until the broader route layer is made dynamic.
     */
    'base_path' => $basePath === '/' ? '/admin' : $basePath,
];
