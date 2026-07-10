<?php

declare(strict_types=1);

/**
 * Verify the 2FA schema in the active CLI database.
 *
 * This verification tool is read-only. It checks table/column/index presence
 * only and never reads, prints or logs TOTP secrets, OTP values, recovery-code
 * plaintext, provisioning URIs, QR data, SMTP passwords or reset tokens.
 */

$basePath = require __DIR__ . '/bootstrap.php';

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$pdo = (new \Zoosper\Core\Database\ConnectionFactory($config, $basePath))->create();
$driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

$required = [
    'admin_user_two_factor' => [
        'id',
        'admin_user_id',
        'method',
        'secret_ciphertext',
        'enabled',
        'confirmed_at',
        'created_at',
        'updated_at',
    ],
    'admin_user_recovery_codes' => [
        'id',
        'admin_user_id',
        'code_hash',
        'used_at',
        'created_at',
    ],
    'admin_two_factor_challenges' => [
        'id',
        'admin_user_id',
        'challenge_token_hash',
        'expires_at',
        'consumed_at',
        'created_at',
    ],
];

print "Zoosper 2FA schema verification\n";
print "================================\n\n";
print "driver: {$driver}\n\n";

$failed = false;
foreach ($required as $table => $columns) {
    $exists = zoosperPhase040TableExists($pdo, $table);
    print '- ' . $table . ': ' . ($exists ? 'exists' : 'missing') . PHP_EOL;

    if (!$exists) {
        $failed = true;
        continue;
    }

    $actualColumns = zoosperPhase040Columns($pdo, $table);
    foreach ($columns as $column) {
        $columnExists = in_array($column, $actualColumns, true);
        print '  - ' . $column . ': ' . ($columnExists ? 'ok' : 'missing') . PHP_EOL;
        if (!$columnExists) {
            $failed = true;
        }
    }
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
if ($failed) {
    print "Recommendation: run `php bin/zoosper migrate`, then run this verifier again.\n";
    exit(2);
}

print "2FA schema is ready for reset tooling and admin reset action.\n";

function zoosperPhase040TableExists(PDO $pdo, string $table): bool
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

/**
 * @return list<string>
 */
function zoosperPhase040Columns(PDO $pdo, string $table): array
{
    $driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver === 'sqlite') {
        $statement = $pdo->query('PRAGMA table_info(' . zoosperPhase040QuoteIdentifier($pdo, $table) . ')');
        return array_map(static fn (array $row): string => (string) $row['name'], $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    $statement = $pdo->prepare('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table ORDER BY ORDINAL_POSITION');
    $statement->execute(['table' => $table]);
    return array_map(static fn (mixed $column): string => (string) $column, $statement->fetchAll(PDO::FETCH_COLUMN));
}

function zoosperPhase040QuoteIdentifier(PDO $pdo, string $identifier): string
{
    if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier)) {
        throw new RuntimeException('Unsafe SQL identifier: ' . $identifier);
    }

    return (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite'
        ? '"' . $identifier . '"'
        : '`' . $identifier . '`';
}
