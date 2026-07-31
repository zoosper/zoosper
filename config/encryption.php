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
    'key' => (string) $env('CACHE_ENCRYPTION_KEY', ''),
    'cipher' => (string) $env('CACHE_ENCRYPTION_CIPHER', 'aes-256-gcm'),
];
