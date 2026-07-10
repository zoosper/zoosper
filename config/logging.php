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
    'path' => $env('LOG_PATH', 'var/log'),
    'default_file' => $env('LOG_FILE', 'system.log'),
    'modules' => [
        'zoosper-admin' => 'admin.log',
        'zoosper-auth' => 'auth.log',
        'zoosper-page' => 'page.log',
        'zoosper-theme' => 'theme.log',
        'zoosper-api' => 'api.log',
    ],
];
