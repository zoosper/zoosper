<?php

declare(strict_types=1);

/**
 * Diagnose module schema discovery and expected table availability.
 *
 * This script is read-only. It uses information_schema for MySQL/MariaDB table
 * checks because some PDO drivers do not reliably bind placeholders in
 * `SHOW TABLES LIKE` statements.
 */

$basePath = dirname(__DIR__);

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

require $basePath . '/vendor/autoload.php';

/**
 * Return true when a table exists in the active database connection.
 */
function zoosperDiagnosticTableExists(\PDO $pdo, string $table): bool
{
    $driver = (string) $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

    if ($driver === 'sqlite') {
        $statement = $pdo->prepare("SELECT name FROM sqlite_master WHERE type = 'table' AND name = :table");
        $statement->execute(['table' => $table]);
        return (bool) $statement->fetchColumn();
    }

    $statement = $pdo->prepare(
        'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table LIMIT 1'
    );
    $statement->execute(['table' => $table]);

    return (bool) $statement->fetchColumn();
}

/**
 * Return all module-owned db_schema.php files found directly on disk.
 *
 * @return list<string>
 */
function zoosperDiagnosticDirectSchemaFiles(string $basePath): array
{
    $files = glob($basePath . '/app/*/config/db_schema.php') ?: [];
    sort($files);

    return array_values($files);
}

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$pdo = (new \Zoosper\Core\Database\ConnectionFactory($config, $basePath))->create();
$modules = new \Zoosper\Core\Module\ModuleRegistry($basePath);

$expectedTables = [
    'url_rewrites',
    'admin_user_two_factor',
    'admin_user_recovery_codes',
    'admin_two_factor_challenges',
];

print "Zoosper migration discovery diagnostics v4\n";
print "========================================\n\n";

print "Discoverable modules and schema files:\n";
$discoveredSchemaFiles = [];
foreach ($modules->enabledModules() as $module) {
    $schemaFile = $module->configPath('db_schema.php');
    $hasSchema = is_file($schemaFile);
    if ($hasSchema) {
        $discoveredSchemaFiles[] = $schemaFile;
    }
    print '- ' . $module->name . ' => ' . ($hasSchema ? $schemaFile : 'no db_schema.php') . PHP_EOL;
}

print "\nDirect schema file scan under app/*/config/db_schema.php:\n";
$directSchemaFiles = zoosperDiagnosticDirectSchemaFiles($basePath);
foreach ($directSchemaFiles as $file) {
    print '- ' . $file . PHP_EOL;
}

print "\nExpected tables:\n";
foreach ($expectedTables as $table) {
    print '- ' . $table . ': ' . (zoosperDiagnosticTableExists($pdo, $table) ? 'exists' : 'missing') . PHP_EOL;
}

print "\nDiagnosis hints:\n";
$missingFromRegistry = array_diff($directSchemaFiles, $discoveredSchemaFiles);
if ($missingFromRegistry !== []) {
    print "- Some app/*/config/db_schema.php files are visible on disk but not via ModuleRegistry. Fix module discovery first." . PHP_EOL;
    foreach ($missingFromRegistry as $file) {
        print "  * Not discovered: {$file}" . PHP_EOL;
    }
} elseif ($directSchemaFiles !== []) {
    print "- Schema files are visible through module discovery. If expected tables are missing, inspect Migrator and DeclarativeSchemaApplier." . PHP_EOL;
} else {
    print "- No app/*/config/db_schema.php files were found. Confirm module files were copied into the repository." . PHP_EOL;
}
