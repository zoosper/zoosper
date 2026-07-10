<?php

declare(strict_types=1);

/**
 * Shared bootstrap for Zoosper CLI tools.
 *
 * Loads Composer and then `.env` values into the CLI runtime before config files
 * are read. This keeps CLI tools aligned with local web/app configuration
 * without requiring manual `export DB_CONNECTION=...` for every command.
 */

$basePath = dirname(__DIR__);

require_once $basePath . '/vendor/autoload.php';

if (class_exists(\Zoosper\Core\Env\EnvFileLoader::class)) {
    \Zoosper\Core\Env\EnvFileLoader::load($basePath);
}

if (!function_exists('env')) {
    /**
     * Return an environment value with a fallback default.
     */
    function env(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }

        $value = getenv($key);
        return $value !== false && $value !== '' ? $value : $default;
    }
}

return $basePath;
