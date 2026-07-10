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
     * Admin 2FA configuration.
     *
     * TWO_FACTOR_ENCRYPTION_KEY should be a strong random secret in production.
     * Never commit production keys, TOTP secrets, OTP values, QR payloads or
     * recovery-code plaintext to source control or logs.
     */
    'issuer' => (string) $env('TWO_FACTOR_ISSUER', 'Zoosper'),
    'period' => (int) $env('TWO_FACTOR_PERIOD', 30),
    'digits' => (int) $env('TWO_FACTOR_DIGITS', 6),
    'window' => (int) $env('TWO_FACTOR_WINDOW', 1),
    'recovery_codes' => (int) $env('TWO_FACTOR_RECOVERY_CODES', 8),
    'encryption_key' => (string) $env('TWO_FACTOR_ENCRYPTION_KEY', (string) $env('APP_KEY', 'change-me-before-production')),
];
