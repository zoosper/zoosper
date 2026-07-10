<?php

declare(strict_types=1);

/**
 * Diagnose database environment/config mismatch for the active CLI runtime.
 *
 * This tool is read-only and intentionally redacts passwords. It helps identify
 * cases where the developer believes MySQL is configured but CLI tools are
 * actually loading SQLite because `.env` is not loaded, DB_CONNECTION is not
 * exported, or config/database.php defaults to sqlite.
 */

$basePath = dirname(__DIR__);

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }

        $value = getenv($key);
        return $value !== false && $value !== '' ? $value : $default;
    }
}

require $basePath . '/vendor/autoload.php';

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$envFile = $basePath . '/.env';
$pdo = (new \Zoosper\Core\Database\ConnectionFactory($config, $basePath))->create();
$driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

print "Zoosper database environment diagnostics\n";
print "========================================\n\n";

print "Files:\n";
print '- .env exists          : ' . (is_file($envFile) ? 'yes' : 'no') . PHP_EOL;
print '- config/database.php : ' . (is_file($basePath . '/config/database.php') ? 'yes' : 'no') . PHP_EOL;

print "\nEnvironment values visible to CLI:\n";
foreach (['APP_ENV', 'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME'] as $key) {
    $value = env($key, '(not set)');
    print '- ' . str_pad($key, 14) . ': ' . (string) $value . PHP_EOL;
}
print '- ' . str_pad('DB_PASSWORD', 14) . ': ' . (env('DB_PASSWORD', '') !== '' ? '(configured, redacted)' : '(not set)') . PHP_EOL;

print "\nConfigRepository values:\n";
foreach ([
    'database.default',
    'database.connections.sqlite.database',
    'database.connections.mysql.host',
    'database.connections.mysql.port',
    'database.connections.mysql.database',
    'database.connections.mysql.username',
] as $key) {
    $value = $config->get($key, '(not configured)');
    print '- ' . str_pad($key, 42) . ': ' . (string) $value . PHP_EOL;
}
print '- ' . str_pad('database.connections.mysql.password', 42) . ': ' . ((string) $config->get('database.connections.mysql.password', '') !== '' ? '(configured, redacted)' : '(not configured)') . PHP_EOL;

print "\nActive PDO connection:\n";
print '- driver              : ' . $driver . PHP_EOL;
if ($driver === 'sqlite') {
    print '- sqlite database     : ' . (string) ($config->get('database.connections.sqlite.database', '(unknown)') ?? '(unknown)') . PHP_EOL;
} else {
    print '- database name       : ' . (string) $pdo->query('SELECT DATABASE()')->fetchColumn() . PHP_EOL;
}

print "\nDiagnosis:\n";
if ($driver === 'mysql') {
    print '- OK: CLI is using MySQL/MariaDB.\n';
    exit(0);
}

print '- CLI is not using MySQL/MariaDB. It is using ' . $driver . '.\n';
print '- If the browser uses MySQL but CLI shows sqlite, PHP-FPM and CLI are loading different environment/config values.\n';
print '- Check `.env`, exported shell variables, and config/database.php default connection.\n';
print '- Run `DB_CONNECTION=mysql php tools/diagnose-env-database.php` to test whether an exported DB_CONNECTION changes the active connection.\n';
