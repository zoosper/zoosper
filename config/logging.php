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
    'enabled' => filter_var($env('LOG_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
    'path' => $env('LOG_PATH', 'var/log'),
    'default_file' => $env('LOG_FILE', 'system.log'),
    'error_file' => $env('ERROR_LOG_FILE', 'exception.log'),
];
