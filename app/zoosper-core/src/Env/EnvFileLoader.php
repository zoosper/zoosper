<?php

declare(strict_types=1);

namespace Zoosper\Core\Env;

/**
 * Loads local `.env` values into the current PHP runtime.
 *
 * This loader is intentionally small and dependency-free. It is suitable for
 * CLI tools and bootstrap entry points that need the same DB/mail/runtime values
 * as the web application. It never prints or logs environment values because
 * they may include database passwords, SMTP passwords, reset tokens or other
 * secrets.
 */
final readonly class EnvFileLoader
{
    /**
     * Load `.env` values from the project root if the file exists.
     *
     * Existing real environment variables are preserved by default so server or
     * shell-level secrets can override local file values.
     */
    public static function load(string $basePath, bool $overrideExisting = false): void
    {
        $file = rtrim($basePath, '/') . '/.env';
        if (!is_file($file) || !is_readable($file)) {
            return;
        }

        foreach (file($file, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            if ($key === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
                continue;
            }

            $value = self::normaliseValue(trim($value));

            if (!$overrideExisting && getenv($key) !== false) {
                continue;
            }

            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv($key . '=' . $value);
        }
    }

    /**
     * Remove wrapping quotes and handle simple escaped newlines.
     */
    private static function normaliseValue(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $first = $value[0];
        $last = $value[strlen($value) - 1];
        if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
            $value = substr($value, 1, -1);
        }

        return str_replace('\\n', "\n", $value);
    }
}
