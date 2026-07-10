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

$basePath = require __DIR__ . '/bootstrap.php';

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$envFile = $basePath . '/.env';
$pdo = (new \Zoosper\Core\Database\ConnectionFactory($config, $basePath))->create();
$driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

print "Zoosper database environment diagnostics\n";
print "========================================\n\n";

print "Files:\n";
print '- .env exists          : ' . (is_file($envFile) ? 'yes' : 'no') . PHP_EOL;
print '- config/database.php : ' . (is_file($basePath . '/config/database.php') ? 'yes' : 'no') . PHP_EOL;

print "\nEnvironment values visible to CLI after bootstrap:\n";
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
print $driver === 'mysql'
    ? "- OK: CLI is using MySQL/MariaDB.\n"
    : "- CLI is not using MySQL/MariaDB. It is using {$driver}. Check `.env` and config/database.php defaults.\n";
