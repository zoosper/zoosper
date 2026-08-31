<?php

declare(strict_types=1);

/**
 * Admin Two-Factor Authentication (TOTP) configuration.
 *
 * Supports key rotation via TWO_FACTOR_PREVIOUS_ENCRYPTION_KEYS (comma-separated).
 */
$previousKeysRaw = trim((string) env('TWO_FACTOR_PREVIOUS_ENCRYPTION_KEYS', ''));
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
    'issuer' => (string) env('TWO_FACTOR_ISSUER', 'Zoosper'),
    'period' => (int) env('TWO_FACTOR_PERIOD', 30),
    'digits' => (int) env('TWO_FACTOR_DIGITS', 6),
    'window' => (int) env('TWO_FACTOR_WINDOW', 1),
    'recovery_codes' => (int) env('TWO_FACTOR_RECOVERY_CODES', 8),
    'encryption_key' => (string) env('TWO_FACTOR_ENCRYPTION_KEY', ''),

    /*
     * List of previous encryption keys, most-recently-retired first, kept
     * temporarily so already-enrolled admins are not locked out when
     * TWO_FACTOR_ENCRYPTION_KEY changes. See the file docblock above for
     * the full rotation procedure.
     */
    'previous_encryption_keys' => $previousKeys,
];








