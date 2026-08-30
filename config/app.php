<?php

declare(strict_types=1);

$release = require __DIR__ . '/version.php';

return [
    'name' => env('APP_NAME', 'Zoosper'),
    'env' => env('APP_ENV', 'local'),
    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
    'version' => env('CMS_VERSION', $release['version']),
];
