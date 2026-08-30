<?php

declare(strict_types=1);

$configuredBasePath = trim((string) env('ADMIN_BASE_PATH', '/admin'));
$basePath = '/' . trim($configuredBasePath, '/');
$configuredIdleTimeout = filter_var(env('ADMIN_SESSION_IDLE_TIMEOUT', 7200), FILTER_VALIDATE_INT);
$idleTimeout = $configuredIdleTimeout === false || $configuredIdleTimeout < 0 ? 7200 : $configuredIdleTimeout;
$configuredAbsoluteLifetime = filter_var(env('ADMIN_SESSION_ABSOLUTE_LIFETIME', 86400), FILTER_VALIDATE_INT);
$absoluteLifetime = $configuredAbsoluteLifetime === false || $configuredAbsoluteLifetime < 0 ? 86400 : $configuredAbsoluteLifetime;

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
    // Maximum seconds an authenticated session may live regardless of activity. 0 disables absolute expiry.
    'session_absolute_lifetime' => $absoluteLifetime,
    'password_minimum_length' => max(8, (int) env('ADMIN_PASSWORD_MINIMUM_LENGTH', 12)),
    'password_minimum_character_classes' => max(1, min(4, (int) env('ADMIN_PASSWORD_MINIMUM_CHARACTER_CLASSES', 2))),
];
