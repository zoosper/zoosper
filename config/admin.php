<?php

declare(strict_types=1);

$env = static function (string $key, mixed $default = null): mixed {
    if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }

    $value = getenv($key);
    return $value !== false && $value !== '' ? $value : $default;
};

$configuredBasePath = trim((string) $env('ADMIN_BASE_PATH', '/admin'));
$basePath = '/' . trim($configuredBasePath, '/');
$configuredIdleTimeout = filter_var($env('ADMIN_SESSION_IDLE_TIMEOUT', 7200), FILTER_VALIDATE_INT);
$idleTimeout = $configuredIdleTimeout === false || $configuredIdleTimeout < 0 ? 7200 : $configuredIdleTimeout;

return [
    /*
     * Admin base path.
     *
     * New admin controllers should avoid hard-coding /admin and should build
     * internal admin URLs from this value instead. Route declarations still use
     * current route-loader paths until the broader route layer is made dynamic.
     */
    'base_path' => $basePath === '/' ? '/admin' : $basePath,
    // Seconds of inactivity before authenticated or pending-2FA state is cleared. 0 disables expiry.
    'session_idle_timeout' => $idleTimeout,
    'password_minimum_length' => max(8, (int) $env('ADMIN_PASSWORD_MINIMUM_LENGTH', 12)),
    'password_minimum_character_classes' => max(1, min(4, (int) $env('ADMIN_PASSWORD_MINIMUM_CHARACTER_CLASSES', 2))),
];
