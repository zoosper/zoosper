<?php

declare(strict_types=1);

$lifetimeSeconds = max(60, (int) env('SESSION_LIFETIME_SECONDS', 28800));
$basePath = dirname(__DIR__, 4);
$configuredPath = trim((string) env('SESSION_STORAGE_PATH', 'var/sessions'));
$storagePath = str_starts_with($configuredPath, '/') || str_contains($configuredPath, '://')
    ? $configuredPath
    : $basePath . '/' . ltrim($configuredPath, '/');

return [
    'driver' => 'file',
    'lifetime' => (int) ceil($lifetimeSeconds / 60),
    'expire_on_close' => false,
    'path' => $storagePath,
    'cookie' => [
        'name' => (string) env('SESSION_NAME', 'ZOOSPERSESSID'),
        'path' => '/',
        'domain' => '',
        'secure' => filter_var(env('SESSION_SECURE', false), FILTER_VALIDATE_BOOL),
        'httponly' => true,
        'samesite' => strtolower((string) env('SESSION_SAMESITE', 'Lax')),
    ],
    'gc_probability' => 1,
    'gc_divisor' => 100,
];
