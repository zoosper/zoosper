<?php

declare(strict_types=1);

/**
 * Zoosper bootstrap: Composer autoloading, .env parsing, and canonical env() helper.
 *
 * Process environment variables retain precedence over .env file entries.
 * Missing keys default to the specified fallback value.
 */

$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';

if (!is_file($composerAutoload)) {
    throw new RuntimeException(
        'Composer autoloader not found at ' . $composerAutoload . '. Run `composer install` (or `composer update`) before starting Zoosper.'
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
        return \Zoosper\Core\Environment\EnvLineParser::parseValue($rawValue);
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
                    $assignment = \Zoosper\Core\Environment\EnvLineParser::parseAssignment($line);
                    if ($assignment === null) {
                        continue;
                    }

                    $name = $assignment->key;

                    // Process-manager/container values are authoritative.
                    // Keep $_ENV and getenv() consistent, then use .env only
                    // for a key that is genuinely missing from both sources.
                    $processValue = getenv($name);
                    if ($processValue !== false) {
                        $_ENV[$name] = $processValue;
                        continue;
                    }

                    if (array_key_exists($name, $_ENV)) {
                        putenv($name . '=' . (string) $_ENV[$name]);
                        continue;
                    }

                    $value = $assignment->value;

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




