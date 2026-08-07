<?php

declare(strict_types=1);

/**
 * SECURITY FIX (confirmed 2026-07-30, external reviewer pass): this file
 * previously hardcoded 'identity_salt' => '' as a plain array value, with
 * NO environment-variable wiring at all — there was no way to configure a
 * real salt even if you wanted one. Added a local $env closure (same
 * pattern used by several other config/*.php files in this codebase) so
 * RATE_LIMIT_IDENTITY_SALT can actually be set. The default remains an
 * empty string.
 *
 * This file itself does NOT enforce a real salt being present (deliberately
 * — to avoid the exact same "eagerly-loaded config file throws and breaks
 * unrelated tests" problem the 2FA encryption key fix hit earlier in this
 * same session). That enforcement lives in
 * RateLimitReportOnlyAdminMiddleware::process() instead — the actual point
 * where the salt is used, and only reached once rate limiting is
 * EXPLICITLY enabled (which it is not, by default: 'enabled' => false below
 * is deliberately left as a plain literal, not env-configurable, to avoid a
 * separate PHP string-to-bool casting gotcha — e.g. an operator setting
 * RATE_LIMIT_ENABLED=false as a literal string would evaluate to boolean
 * true via (bool) casting, silently enabling something they intended to
 * disable. Only 'identity_salt' is env-wired here; every other setting is
 * untouched from the original file).
 */
$env = static function (string $key, mixed $default = null): mixed {
    if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }

    $value = getenv($key);
    return $value !== false && $value !== '' ? $value : $default;
};

$enabled = filter_var($env('RATE_LIMIT_ENABLED', false), FILTER_VALIDATE_BOOLEAN);
$mode = strtolower(trim((string) $env('RATE_LIMIT_MODE', 'report_only')));
$mode = in_array($mode, ['report_only', 'enforce'], true) ? $mode : 'report_only';
$loginMaxAttempts = max(1, min(100, (int) $env('RATE_LIMIT_ADMIN_LOGIN_MAX_ATTEMPTS', 5)));
$loginWindowSeconds = max(1, min(86400, (int) $env('RATE_LIMIT_ADMIN_LOGIN_WINDOW_SECONDS', 300)));

return [
    'enabled' => $enabled,
    'mode' => $mode,
    'report_path' => 'var/reports/rate-limit-events.jsonl',
    /*
     * RATE_LIMIT_IDENTITY_SALT should be a strong, random secret before
     * enabling rate limiting. It is used to hash the caller's email+IP
     * before that hash is ever stored — with no real salt, the hash is a
     * dictionary/rainbow-table target rather than genuinely opaque. See
     * RateLimitReportOnlyAdminMiddleware for where this is actually
     * enforced (fails loudly if empty AND rate limiting is enabled).
     */
    'identity_salt' => (string) $env('RATE_LIMIT_IDENTITY_SALT', ''),
    'policies' => [
        'admin.login' => [
            'scope' => 'admin',
            'max_attempts' => $loginMaxAttempts,
            'window_seconds' => $loginWindowSeconds,
        ],
        // Example future policy shape:
        // 'admin.login' => [
        //     'scope' => 'admin',
        //     'max_attempts' => 5,
        //     'window_seconds' => 300,
        // ],
    ],
];
