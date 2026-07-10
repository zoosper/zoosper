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
    'enabled' => filter_var($env('ADMIN_2FA_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
    'issuer' => (string) $env('ADMIN_2FA_ISSUER', 'Zoosper'),
    'totp_digits' => 6,
    'totp_period' => 30,
    'totp_algorithm' => 'SHA1',
    'recovery_code_count' => 10,
    'recovery_code_bytes' => 10,
];
