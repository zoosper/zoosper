<?php

declare(strict_types=1);

/**
 * Print redacted database connection diagnostics for the active CLI runtime.
 *
 * This tool is read-only. It intentionally avoids printing database passwords
 * or secret connection strings. Use it to confirm whether CLI commands are
 * using SQLite, MySQL, or another configured PDO driver.
 */

$basePath = require __DIR__ . '/bootstrap.php';

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$policyFile = $basePath . '/config/database_policy.php';
$policy = is_file($policyFile) ? require $policyFile : ['mysql_family_drivers' => ['mysql'], 'allow_sqlite_in_local' => true];
$pdo = (new \Zoosper\Core\Database\ConnectionFactory($config, $basePath))->create();
$driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
$mysqlDrivers = $policy['mysql_family_drivers'] ?? ['mysql'];
$appEnv = strtolower((string) ($config->get('app.env', env('APP_ENV', 'local')) ?? 'local'));
$isLocal = in_array($appEnv, ['local', 'dev', 'development', 'test', 'testing'], true);

print "Zoosper database connection diagnostics\n";
print "======================================\n\n";
print "driver               : {$driver}\n";
print "configured_connection: " . (string) ($config->get('database.default', '(not configured)') ?? '(not configured)') . "\n";
print "app_env              : {$appEnv}\n";

if ($driver === 'sqlite') {
    print "sqlite_database      : " . (string) ($config->get('database.connections.sqlite.database', '(unknown)') ?? '(unknown)') . "\n";
} else {
    print "database_name        : " . (string) $pdo->query('SELECT DATABASE()')->fetchColumn() . "\n";
}

print "\nPolicy:\n";
if (in_array($driver, $mysqlDrivers, true)) {
    print "- OK: MySQL/MariaDB is the supported production path.\n";
} elseif ($driver === 'sqlite' && $isLocal && (bool) ($policy['allow_sqlite_in_local'] ?? true)) {
    print "- WARNING: SQLite is active. This is acceptable for local/dev smoke tests only. Use MySQL/MariaDB for production and serious module testing.\n";
} else {
    print "- WARNING: Non-MySQL driver detected. Zoosper is MySQL/MariaDB-first.\n";
}

print "\nNote: passwords and full DSNs are intentionally not printed.\n";
