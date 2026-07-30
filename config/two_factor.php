<?php

declare(strict_types=1);

/**
 * REVISED (2026-07-30): an earlier version of this security fix made this
 * file throw a RuntimeException directly when no encryption key was
 * configured. That broke because ConfigRepository::fromPath() eagerly
 * `require`s EVERY file in config/ the moment ANY config is requested (see
 * that class's fromPath() method) — so completely unrelated code (database
 * connection tests, module discovery, the frontend boot test) was also
 * triggering this file's throw, even though none of them use 2FA at all.
 *
 * The real fix now lives where the key is actually USED to build working
 * crypto: SecretProtector's factory in
 * app/zoosper-two-factor/config/services.php. This file simply returns
 * whatever key is configured (or an empty string if none is) — completely
 * safe to eagerly load alongside every other config file, exactly like
 * every other config/*.php file in this codebase.
 *
 * SEPARATE, LARGER QUESTION RAISED ALONGSIDE THIS FIX (not resolved here):
 * this file — and several other clearly feature-specific config files
 * (url_rewrite.php, mail.php, editor.php, etc.) — currently live in this
 * shared root config/ folder rather than inside their owning module (e.g.
 * app/zoosper-two-factor/config/two_factor.php), unlike routes, services,
 * controllers, and migrations, which have already been made module-owned
 * elsewhere in this codebase this session. Moving feature-specific config
 * to live inside its owning module is a real, deliberate future
 * architectural phase — not something to rush as a side effect of this bug
 * fix.
 *
 * Original security concern (still fully addressed, just enforced in the
 * correct place now): this file previously fell back to an insecure,
 * publicly-visible placeholder literal as the 2FA encryption key if neither
 * TWO_FACTOR_ENCRYPTION_KEY nor APP_KEY was set (the exact prior value is
 * visible in this file's git history, deliberately not repeated verbatim
 * here since a regression test asserts it no longer appears in this file's
 * source). That literal fallback has been removed from this file. See
 * SecretProtector's factory for where "no real key configured" is now
 * actually enforced, at the point crypto is genuinely performed.
 */
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
     *
     * 'encryption_key' below may be an empty string if nothing is configured
     * — this file itself does NOT enforce a real key being present. That
     * enforcement happens in SecretProtector's service factory, the actual
     * point where this value is used to perform real encryption/decryption.
     */
    'issuer' => (string) $env('TWO_FACTOR_ISSUER', 'Zoosper'),
    'period' => (int) $env('TWO_FACTOR_PERIOD', 30),
    'digits' => (int) $env('TWO_FACTOR_DIGITS', 6),
    'window' => (int) $env('TWO_FACTOR_WINDOW', 1),
    'recovery_codes' => (int) $env('TWO_FACTOR_RECOVERY_CODES', 8),
    'encryption_key' => (string) $env('TWO_FACTOR_ENCRYPTION_KEY', (string) $env('APP_KEY', '')),
];
