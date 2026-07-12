<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$schemaPath = $basePath . '/database/schema/admin_user_locale.php';

print "Zoosper admin user locale schema apply\n";
print "======================================\n\n";

if (!is_file($schemaPath)) {
    fwrite(STDERR, "Missing schema file: {$schemaPath}\n");
    exit(2);
}

$schema = require $schemaPath;
if (!is_array($schema)) {
    fwrite(STDERR, "Schema file must return an array.\n");
    exit(2);
}

$pdo = zoosper_schema_pdo($basePath);
$table = (string) ($schema['table'] ?? 'admin_users');
$columns = is_array($schema['columns'] ?? null) ? $schema['columns'] : [];
$added = 0;

foreach ($columns as $column => $definition) {
    if (zoosper_column_exists($pdo, $table, (string) $column)) {
        print "- {$table}.{$column} already exists\n";
        continue;
    }

    $sql = 'ALTER TABLE `' . str_replace('`', '``', $table) . '` ADD COLUMN `' . str_replace('`', '``', (string) $column) . '` ' . (string) ($definition['definition'] ?? 'VARCHAR(16) NULL');
    $after = (string) ($definition['after'] ?? '');
    if ($after !== '' && zoosper_column_exists($pdo, $table, $after)) {
        $sql .= ' AFTER `' . str_replace('`', '``', $after) . '`';
    }

    $pdo->exec($sql);
    $added++;
    print "- added {$table}.{$column}\n";
}

print "\nColumns added: {$added}\n";
print "Result: OK\n";

function zoosper_schema_pdo(string $basePath): PDO
{
    $bootstrapPath = $basePath . '/tools/page-content-schema-db.php';
    if (is_file($bootstrapPath)) {
        require_once $bootstrapPath;
        if (function_exists('zoosper_page_content_schema_pdo')) {
            return zoosper_page_content_schema_pdo($basePath);
        }
    }

    $configPath = $basePath . '/config/database.php';
    $config = is_file($configPath) ? require $configPath : [];
    if (!is_array($config)) {
        throw new RuntimeException('Database config must return an array.');
    }

    $connectionName = (string) ($config['default'] ?? 'mysql');
    $connection = $config['connections'][$connectionName] ?? $config[$connectionName] ?? $config;
    if (!is_array($connection)) {
        throw new RuntimeException('Unable to resolve database connection configuration.');
    }

    $driver = (string) ($connection['driver'] ?? 'mysql');
    $host = (string) ($connection['host'] ?? '127.0.0.1');
    $port = (string) ($connection['port'] ?? '3306');
    $database = (string) ($connection['database'] ?? 'zoosper');
    $charset = (string) ($connection['charset'] ?? 'utf8mb4');
    $username = (string) ($connection['username'] ?? $connection['user'] ?? 'root');
    $password = (string) ($connection['password'] ?? '');

    $dsn = $driver === 'sqlite'
        ? 'sqlite:' . $database
        : sprintf('%s:host=%s;port=%s;dbname=%s;charset=%s', $driver, $host, $port, $database, $charset);

    return new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
}

function zoosper_column_exists(PDO $pdo, string $table, string $column): bool
{
    $statement = $pdo->prepare('SHOW COLUMNS FROM `' . str_replace('`', '``', $table) . '` LIKE :column');
    $statement->execute(['column' => $column]);

    return (bool) $statement->fetch(PDO::FETCH_ASSOC);
}
