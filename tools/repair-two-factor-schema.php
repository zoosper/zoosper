<?php

declare(strict_types=1);

/**
 * Repair missing 2FA schema columns in the active MySQL database.
 *
 * This tool performs metadata checks and applies only additive ALTER TABLE
 * statements. It never reads, prints or logs TOTP secrets, OTP values,
 * recovery-code plaintext, provisioning URIs, QR data, SMTP passwords or reset
 * tokens.
 */

$basePath = require __DIR__ . '/bootstrap.php';

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$pdo = (new \Zoosper\Core\Database\ConnectionFactory($config, $basePath))->create();
$driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

print "Zoosper 2FA schema repair\n";
print "=========================\n\n";
print "driver: {$driver}\n\n";

if ($driver !== 'mysql') {
    fwrite(STDERR, "This repair tool is intended for MySQL/MariaDB only. Active driver: {$driver}.\n");
    exit(2);
}

$repairs = [
    'admin_user_two_factor' => [
        'enabled' => 'ALTER TABLE `admin_user_two_factor` ADD COLUMN `enabled` TINYINT(1) NOT NULL DEFAULT 0 AFTER `secret_ciphertext`',
        'confirmed_at' => 'ALTER TABLE `admin_user_two_factor` ADD COLUMN `confirmed_at` DATETIME NULL AFTER `enabled`',
    ],
    'admin_two_factor_challenges' => [
        'consumed_at' => 'ALTER TABLE `admin_two_factor_challenges` ADD COLUMN `consumed_at` DATETIME NULL AFTER `expires_at`',
    ],
];

$changed = false;
foreach ($repairs as $table => $columns) {
    if (!zoosperPhase041TableExists($pdo, $table)) {
        print "- {$table}: missing table, run php bin/zoosper migrate first.\n";
        continue;
    }

    foreach ($columns as $column => $sql) {
        if (zoosperPhase041ColumnExists($pdo, $table, $column)) {
            print "- {$table}.{$column}: already exists\n";
            continue;
        }

        $pdo->exec($sql);
        $changed = true;
        print "- {$table}.{$column}: added\n";
    }
}

print "\nResult: " . ($changed ? 'schema repaired' : 'no repairs required') . PHP_EOL;
print "Next: php tools/verify-two-factor-schema.php\n";

function zoosperPhase041TableExists(PDO $pdo, string $table): bool
{
    $statement = $pdo->prepare('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table LIMIT 1');
    $statement->execute(['table' => $table]);
    return (bool) $statement->fetchColumn();
}

function zoosperPhase041ColumnExists(PDO $pdo, string $table, string $column): bool
{
    $statement = $pdo->prepare('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column LIMIT 1');
    $statement->execute(['table' => $table, 'column' => $column]);
    return (bool) $statement->fetchColumn();
}
