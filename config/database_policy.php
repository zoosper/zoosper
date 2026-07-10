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
     * Zoosper is MySQL/MariaDB-first.
     *
     * SQLite can be useful for very early smoke tests, but it should not be the
     * supported production path. Enforcing a MySQL-family driver for production
     * avoids maintaining divergent SQL behaviour for schema, indexes, locking,
     * JSON/text handling, and operational diagnostics.
     */
    'production_driver' => (string) $env('DATABASE_PRODUCTION_DRIVER', 'mysql'),
    'allow_sqlite_in_local' => filter_var($env('DATABASE_ALLOW_SQLITE_LOCAL', true), FILTER_VALIDATE_BOOLEAN),
    'enforce_mysql_in_production' => filter_var($env('DATABASE_ENFORCE_MYSQL_PRODUCTION', true), FILTER_VALIDATE_BOOLEAN),
    'mysql_family_drivers' => ['mysql'],
];
