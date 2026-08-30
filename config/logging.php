<?php

declare(strict_types=1);

return [
    'enabled' => filter_var(env('LOG_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
    'path' => env('LOG_PATH', 'var/log'),
    'default_file' => env('LOG_FILE', 'system.log'),
    'error_file' => env('ERROR_LOG_FILE', 'exception.log'),
];
