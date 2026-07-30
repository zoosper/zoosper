<?php

declare(strict_types=1);

/**
 * SECURITY FIX (confirmed 2026-07-30, external reviewer pass): this file
 * previously fell back to an insecure, publicly-visible placeholder literal
 * as the 2FA encryption key if neither TWO_FACTOR_ENCRYPTION_KEY nor
 * APP_KEY was set (the exact prior value is visible in this file's git
 * history, deliberately not repeated verbatim here since a regression test
 * asserts that value no longer appears anywhere in this file's source).
 *
 * This file does NOT enforce a real key being present. That enforcement
 * lives in SecretProtector's service factory instead — the actual point
 * where the key is used to build real crypto.
 *
 * KEY ROTATION SUPPORT (confirmed 2026-07-30, real production lockout
 * incident): added 'previous_encryption_keys', read from
 * TWO_FACTOR_PREVIOUS_ENCRYPTION_KEYS. This directly fixes a real incident
 * where an admin who enrolled in 2FA before TWO_FACTOR_ENCRYPTION_KEY was
 * first set (or before it was later changed) became permanently unable to
 * log in, because SecretProtector::reveal() only ever tried a single key.
 *
 * TWO_FACTOR_PREVIOUS_ENCRYPTION_KEYS accepts one or more OLD key values,
 * comma-separated, e.g.:
 *   TWO_FACTOR_PREVIOUS_ENCRYPTION_KEYS=old-key-1,old-key-2
 * Whenever TWO_FACTOR_ENCRYPTION_KEY is changed, move its OLD value into
 * this list (prepending, so the most recent previous key is tried first)
 * so already-enrolled admins can still log in. Once every admin has
 * logged in at least once since the rotation (each successful login
 * opportunistically re-encrypts with the current key — see
 * AdminTwoFactorEnrollmentService::revealSecret()), the old key(s) can be
 * safely removed from this list.
 */
$env = static function (string $key, mixed $default = null): mixed {
    if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }

    $value = getenv($key);
    return $value !== false && $value !== '' ? $value : $default;
};

$previousKeysRaw = trim((string) $env('TWO_FACTOR_PREVIOUS_ENCRYPTION_KEYS', ''));
$previousKeys = $previousKeysRaw === ''
    ? []
    : array_values(array_filter(array_map('trim', explode(',', $previousKeysRaw)), static fn (string $key): bool => $key !== ''));

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
    'encryption_key' => (string) $env('TWO_FACTOR_ENCRYPTION_KEY', (string) $env('APP_KEY', '')),

    /*
     * List of previous encryption keys, most-recently-retired first, kept
     * temporarily so already-enrolled admins are not locked out when
     * TWO_FACTOR_ENCRYPTION_KEY changes. See the file docblock above for
     * the full rotation procedure.
     */
    'previous_encryption_keys' => $previousKeys,
];
