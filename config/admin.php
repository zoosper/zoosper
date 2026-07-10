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
    /*
     * Configurable admin front name.
     *
     * This is intentionally not treated as a primary security control. It can
     * reduce automated noise, but Zoosper must still rely on authentication,
     * ACL, CSRF, session hardening, audit logging and 2FA.
     */
    'path' => trim((string) $env('ADMIN_PATH', 'admin'), '/'),
];
