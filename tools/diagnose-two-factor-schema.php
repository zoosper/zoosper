<?php

declare(strict_types=1);

/**
 * Diagnose 2FA tables in the active CLI database.
 *
 * This tool is read-only and prints table presence/counts only. It never reads
 * or prints TOTP secrets, OTP values, recovery-code plaintext, provisioning
 * URIs, QR data, SMTP passwords or reset tokens. It uses the shared CLI
 * bootstrap so `.env` is loaded consistently.
 */

$basePath = require __DIR__ . '/bootstrap.php';

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$pdo = (new \Zoosper\Core\Database\ConnectionFactory($config, $basePath))->create();
$driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
$tables = [
    'admin_user_two_factor',
    'admin_user_recovery_codes',
    'admin_two_factor_challenges',
];

print "Zoosper 2FA schema diagnostics\n";
print "=============================\n\n";
print "driver: {$driver}\n\n";

foreach ($tables as $table) {
    $exists = zoosperPhase039TableExists($pdo, $table);
    print '- ' . $table . ': ' . ($exists ? 'exists' : 'missing');

    if ($exists) {
        $count = (int) $pdo->query('SELECT COUNT(*) FROM ' . zoosperPhase039QuoteIdentifier($pdo, $table))->fetchColumn();
        print ' (' . $count . ' rows)';
    }

    print PHP_EOL;
}

$missing = array_values(array_filter($tables, static fn (string $table): bool => !zoosperPhase039TableExists($pdo, $table)));
print "\nRecommendation:\n";
if ($missing === []) {
    print "- 2FA schema is available in the active CLI database.\n";
} else {
    print "- Missing 2FA tables detected. Run `php bin/zoosper migrate` for the active CLI database, then re-run this tool.\n";
}

function zoosperPhase039TableExists(PDO $pdo, string $table): bool
{
    $driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver === 'sqlite') {
        $statement = $pdo->prepare("SELECT name FROM sqlite_master WHERE type = 'table' AND name = :table LIMIT 1");
        $statement->execute(['table' => $table]);
        return (bool) $statement->fetchColumn();
    }

    $statement = $pdo->prepare('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table LIMIT 1');
    $statement->execute(['table' => $table]);
    return (bool) $statement->fetchColumn();
}

function zoosperPhase039QuoteIdentifier(PDO $pdo, string $identifier): string
{
    if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier)) {
        throw new RuntimeException('Unsafe SQL identifier: ' . $identifier);
    }

    return (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite'
        ? '"' . $identifier . '"'
        : '`' . $identifier . '`';
}
