<?php

declare(strict_types=1);

/**
 * Assert that the active database driver is acceptable for the current runtime.
 *
 * This tool is read-only. It prints only non-secret connection metadata and
 * never prints passwords, DSNs, SMTP secrets, OTPs, TOTP secrets,
 * recovery-code plaintext, reset tokens, provisioning URIs or QR data.
 */

$basePath = require __DIR__ . '/bootstrap.php';

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$policy = require $basePath . '/config/database_policy.php';
$pdo = (new \Zoosper\Core\Database\ConnectionFactory($config, $basePath))->create();
$driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
$appEnv = strtolower((string) ($config->get('app.env', env('APP_ENV', 'local')) ?? 'local'));
$mysqlDrivers = $policy['mysql_family_drivers'] ?? ['mysql'];
$enforceProduction = (bool) ($policy['enforce_mysql_in_production'] ?? true);
$allowSqliteLocal = (bool) ($policy['allow_sqlite_in_local'] ?? true);

print "Zoosper production database policy check\n";
print "=======================================\n\n";
print "app_env              : {$appEnv}\n";
print "active_driver        : {$driver}\n";
print "production_driver    : " . (string) ($policy['production_driver'] ?? 'mysql') . "\n";
print "allow_sqlite_local   : " . ($allowSqliteLocal ? 'yes' : 'no') . "\n";
print "enforce_production   : " . ($enforceProduction ? 'yes' : 'no') . "\n\n";

$isMysqlFamily = in_array($driver, $mysqlDrivers, true);
$isLocal = in_array($appEnv, ['local', 'dev', 'development', 'test', 'testing'], true);

if ($isMysqlFamily) {
    print "Result: OK - active database driver is MySQL-family.\n";
    exit(0);
}

if ($isLocal && $driver === 'sqlite' && $allowSqliteLocal) {
    print "Result: WARNING - SQLite is allowed for local/dev only. Use MySQL/MariaDB for production.\n";
    exit(0);
}

if (!$isLocal && $enforceProduction) {
    fwrite(STDERR, "Result: FAIL - production environments must use MySQL/MariaDB. Active driver: {$driver}.\n");
    exit(2);
}

print "Result: WARNING - non-MySQL driver detected. MySQL/MariaDB is the supported production path.\n";
