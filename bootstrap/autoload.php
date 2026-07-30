<?php

declare(strict_types=1);

/**
 * Zoosper bootstrap: Composer autoloading + the global env() helper.
 *
 * FIX (confirmed 2026-07-30, external reviewer pass) — four confirmed
 * issues fixed together, since they're all part of this same small file:
 *
 * 1. DEAD FALLBACK AUTOLOADER REMOVED. The previous SPL fallback (used
 *    only when vendor/autoload.php was missing) mapped just 6 of this
 *    project's 12+ module namespaces (Core, Api, Admin, Auth, Site, Page —
 *    Theme, Mail, TwoFactor, UrlRewrite, Install, Media were absent), and
 *    could not load required third-party dependencies (HTMLPurifier,
 *    Latte, marko/framework) at all. It could never actually run the
 *    application — it only delayed the same unrecoverable failure to a
 *    more confusing point deeper in the request. Replaced with a single,
 *    clear, fail-fast error explaining exactly what to run.
 *
 * 2. env() OPERATOR-PRECEDENCE BUG FIXED. The previous final line was:
 *      return $_ENV[$key] ?? getenv($key) ?: $default;
 *    PHP's ?? binds tighter than ?:, so this actually evaluated as
 *    ($_ENV[$key] ?? getenv($key)) ?: $default — meaning any value that
 *    was explicitly SET but falsy (e.g. DEBUG=0, or an intentionally
 *    empty string) silently collapsed to $default. DEBUG=0 with a truthy
 *    default could mean debug mode being unintentionally ON in
 *    production. Fixed with explicit array_key_exists() checks instead of
 *    relying on ??/?: precedence together.
 *
 * 3. .ENV PARSER FIXED. The previous line-parser had three real bugs,
 *    fixed together below:
 *    - Inline comments were never stripped (DB_PASS=secret # note yielded
 *      the literal value "secret # note").
 *    - trim($value, " \t\n\r\0\x0B\"'") stripped quote CHARACTERS from
 *      both ends independently (not a matched pair), so a value that
 *      legitimately ends in a bare quote character (e.g. a password
 *      ending in ' or ") got silently mangled. Fixed to only strip quotes
 *      when the value both STARTS and ENDS with the SAME quote character
 *      (a genuinely-quoted value), never an unmatched leading/trailing
 *      quote character alone.
 *    - Parsed values were only ever written to $_ENV, never passed to
 *      putenv() — so this same function's own getenv() fallback branch
 *      could read a DIFFERENT, inconsistent value than what was actually
 *      parsed from .env. Fixed to call putenv() alongside $_ENV.
 *    Also added: support for a leading `export ` keyword (a common .env
 *    dialect). Deliberately NOT added (genuinely larger features, not
 *    simple bug fixes, out of scope here): multiline values and
 *    ${VAR}-style variable interpolation.
 *
 * 4. NO function_exists() GUARD. The global env() function had no guard
 *    at all — if any Composer dependency (or user/module code) ever
 *    defined its own global env() function first, this file would fatal
 *    with a redeclaration error the moment it was loaded. Wrapped the
 *    entire function definition in function_exists('env').
 *
 * NOT addressed here (confirmed real by a separate reviewer finding, but
 * out of scope for this fix — requires reviewing files not yet available):
 * a reported THIRD, competing env implementation pair — Core\Bootstrap\
 * EnvLoader and a Core\Env\ namespace — reportedly exist alongside this
 * global env(). Consolidating those requires reviewing those files
 * directly before any of them can be safely changed or removed — not
 * attempted blind in this fix.
 */

$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';

if (!is_file($composerAutoload)) {
    throw new RuntimeException(
        'Composer autoloader not found at ' . $composerAutoload . '. Run `composer install` '
        . '(or `composer update`) before starting Zoosper. A previous fallback autoloader was '
        . 'removed here because it only mapped 6 of this project\'s 12+ module namespaces and '
        . 'could not load required third-party dependencies (HTMLPurifier, Latte, etc.) at all — '
        . 'it could never actually run the application, it only delayed this same failure to a '
        . 'more confusing point further into the request.'
    );
}

require $composerAutoload;

if (!function_exists('zoosperParseEnvValue')) {
    /**
     * Parse a single raw .env value: strip a genuinely matched pair of
     * quotes (never an unmatched leading/trailing quote character), and
     * strip a trailing inline comment ONLY for unquoted values (a quoted
     * value's contents, including any literal #, are preserved exactly).
     */
    function zoosperParseEnvValue(string $rawValue): string
    {
        if ($rawValue === '') {
            return '';
        }

        $first = $rawValue[0];
        $length = strlen($rawValue);

        if (($first === '"' || $first === "'") && $length >= 2 && $rawValue[$length - 1] === $first) {
            return substr($rawValue, 1, -1);
        }

        if (preg_match('/^(.*?)\s+#.*$/', $rawValue, $matches) === 1) {
            return trim($matches[1]);
        }

        return $rawValue;
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        static $loaded = false;

        if (!$loaded) {
            $envFile = dirname(__DIR__) . '/.env';

            if (is_file($envFile)) {
                foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                    $line = trim($line);

                    if ($line === '' || str_starts_with($line, '#')) {
                        continue;
                    }

                    // Support (and strip) a leading "export " keyword — a
                    // common .env dialect (bash-style exports).
                    if (str_starts_with($line, 'export ')) {
                        $line = trim(substr($line, 7));
                    }

                    if (!str_contains($line, '=')) {
                        continue;
                    }

                    [$name, $rawValue] = explode('=', $line, 2);
                    $name = trim($name);
                    $rawValue = trim($rawValue);

                    if ($name === '') {
                        continue;
                    }

                    $value = zoosperParseEnvValue($rawValue);

                    $_ENV[$name] = $value;
                    putenv($name . '=' . $value);
                }
            }

            $loaded = true;
        }

        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        $value = getenv($key);

        return $value !== false ? $value : $default;
    }
}
