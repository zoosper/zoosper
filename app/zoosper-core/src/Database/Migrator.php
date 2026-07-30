<?php

declare(strict_types=1);

namespace Zoosper\Core\Database;

use Closure;
use PDO;
use ReflectionFunction;
use ReflectionMethod;
use RuntimeException;
use Throwable;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\Schema\SchemaLoader;
use Zoosper\Core\Schema\SchemaMigrator;
use Zoosper\Core\Schema\SchemaSnapshotRepository;

/**
 * Runs traditional migration files and module-owned declarative schema files.
 *
 * This migrator keeps Zoosper marketplace-module friendly by treating
 * `bin/zoosper migrate` as the single schema update entry point. Core and
 * marketplace modules can own schema in `config/db_schema.php`, while explicit
 * migration files remain supported for one-off data/schema changes.
 *
 * Phase 1.29: module-owned schema is now applied by the unified `Schema/` engine
 * (validated by SchemaValidator and audited by SchemaSnapshotRepository),
 * replacing the previous create-only DeclarativeSchemaApplier. There is now a
 * single declarative schema engine used by both `bin/zoosper migrate` and
 * `bin/zoosper-schema apply`.
 *
 * Phase 1.40c: traditional migration *files* are now discovered from two
 * places, not just the root `database/migrations/` folder:
 *
 * 1. The root `database/migrations/` folder — reserved for core bootstrap
 *    migrations that are not owned by any single feature module (e.g. the
 *    no-op continuity migration that predates the declarative schema engine).
 * 2. Each enabled module's own `database/migrations/` folder — e.g.
 *    `app/zoosper-auth/database/migrations/`. This lets a module own its
 *    schema history the same way it already owns its declarative
 *    `config/db_schema.php`, its routes, its controllers, etc. Removing a
 *    module removes its migration history with it; a marketplace module can
 *    ship migrations the exact same way a first-party module does.
 *
 * Migration identity is still tracked by filename alone (see
 * hasMigrationRun()/markMigrationRun()), NOT by folder path. This means
 * relocating an already-applied migration file from the root folder into its
 * owning module's folder is always safe: the migrations ledger recognises it
 * by the same filename and will not re-run it.
 *
 * Files from both locations are merged and sorted by filename (not full
 * path), so migrations still execute in their original chronological order
 * regardless of which module folder they now live in.
 *
 * Supported migration file formats:
 *
 * - `return ['CREATE TABLE ...', 'CREATE INDEX ...'];`
 * - `return 'CREATE TABLE ...';`
 * - `return static function (PDO $pdo): void { ... };`
 * - `return static function (PDO $pdo, string $driver): void { ... };`
 * - `return ['up' => static function (PDO $pdo): void { ... }];`
 * - object with `up(PDO $pdo): void`.
 * - object with `up(PDO $pdo, string $driver): void`.
 *
 * PCI-aware reminder: migration code must never seed OTP values, plaintext
 * recovery codes, TOTP secrets or other authentication secrets into logs or
 * schema defaults. Authentication secrets must be protected at the service
 * layer and written only as protected payloads or hashes.
 *
 * BUG FIX (confirmed 2026-07-30): tableExists() previously ran
 * `SHOW TABLES LIKE :table` as a bound, prepared statement on MySQL. This
 * had been silently working ONLY because PDO::ATTR_EMULATE_PREPARES was
 * previously left at its (insecure, client-side-emulated) default — see
 * ConnectionFactory's own EMULATE_PREPARES security fix earlier in this
 * project's history. Once real, server-side prepared statements were
 * correctly enabled, this broke: `SHOW TABLES` is a MySQL administrative/
 * utility statement that MySQL's real binary prepared-statement protocol
 * does not support parameter binding for at all (only a limited set of
 * statement types — mostly SELECT/INSERT/UPDATE/DELETE — support real
 * bound parameters). MySQL's server represents an unresolvable bound
 * placeholder internally as a literal `?`, which is exactly the confusing
 * "syntax error ... near '?'" this produced — the placeholder itself was
 * never a literal `?` character anywhere in this codebase's own source; it
 * was MySQL's own error message vocabulary for "I don't know what to do
 * with this parameter here."
 *
 * Fixed by querying `information_schema.TABLES` instead — a real, ordinary
 * SELECT statement that fully supports real bound parameters, and is
 * already the exact pattern several of this project's own migration files
 * (e.g. acl_tree_metadata's addMysqlColumnIfMissing()) already use
 * correctly for the equivalent column-existence check.
 */
final class Migrator
{
    private string $migrationsPath;
    private string $basePath;
    private ?ModuleRegistry $modules;
    private ?string $migrationColumn = null;
    private ?string $timestampColumn = null;

    /**
     * @param string|null $pathOrBasePath Existing callers may pass either the
     *                                    project root or database/migrations.
     */
    public function __construct(
        private readonly PDO $pdo,
        ?string $pathOrBasePath = null,
        ?ModuleRegistry $modules = null,
    ) {
        $pathOrBasePath ??= dirname(__DIR__, 5);

        if (is_dir(rtrim($pathOrBasePath, '/') . '/database/migrations')) {
            $this->basePath = rtrim($pathOrBasePath, '/');
            $this->migrationsPath = $this->basePath . '/database/migrations';
        } else {
            $this->migrationsPath = rtrim($pathOrBasePath, '/');
            $this->basePath = dirname($this->migrationsPath, 2);
        }

        $this->modules = $modules;
    }

    /**
     * Apply pending file migrations and then every enabled module schema file.
     */
    public function migrate(): void
    {
        $this->ensureMigrationTable();
        $this->applyFileMigrations();
        $this->applyModuleSchemas();
    }

    /**
     * Execute all pending migration files in filename order, drawn from both
     * the root database/migrations/ folder and every enabled module's own
     * database/migrations/ folder (Phase 1.40c).
     */
    private function applyFileMigrations(): void
    {
        $files = $this->collectMigrationFiles();

        foreach ($files as $file) {
            $migrationName = basename($file);
            if ($this->hasMigrationRun($migrationName)) {
                continue;
            }

            $migration = require $file;
            $this->executeMigrationPayload($migration, $file);
            $this->markMigrationRun($migrationName);
        }
    }

    /**
     * Gather migration file paths from the root migrations folder and from
     * every enabled module's own database/migrations/ folder, then sort by
     * filename (not full path) so chronological order is preserved
     * regardless of which folder a file lives in.
     *
     * @return list<string>
     */
    private function collectMigrationFiles(): array
    {
        $files = glob($this->migrationsPath . '/*.php') ?: [];

        foreach ($this->moduleRegistry()->enabledModules() as $module) {
            $moduleMigrationsPath = rtrim($module->path, '/\\') . '/database/migrations';
            if (!is_dir($moduleMigrationsPath)) {
                continue;
            }

            foreach (glob($moduleMigrationsPath . '/*.php') ?: [] as $moduleFile) {
                $files[] = $moduleFile;
            }
        }

        usort($files, static fn (string $a, string $b): int => basename($a) <=> basename($b));

        return $files;
    }

    /**
     * Apply every enabled module-owned config/db_schema.php via the unified
     * Schema/ engine (validated + snapshotted).
     */
    private function applyModuleSchemas(): void
    {
        $registry = (new SchemaLoader($this->moduleRegistry()))->load();
        $snapshots = new SchemaSnapshotRepository($this->pdo);
        (new SchemaMigrator($this->pdo, $this->driver(), $snapshots))->apply($registry);
    }

    /**
     * Return the shared ModuleRegistry instance, creating a default one if
     * none was supplied via the constructor.
     */
    private function moduleRegistry(): ModuleRegistry
    {
        return $this->modules ??= new ModuleRegistry($this->basePath);
    }

    /**
     * Execute a supported migration payload.
     */
    private function executeMigrationPayload(mixed $migration, string $file): void
    {
        if ($migration === [] || $migration === null) {
            return;
        }

        if (is_string($migration)) {
            $this->execSql($migration);
            return;
        }

        if (is_callable($migration)) {
            $this->invokeCallable($migration);
            return;
        }

        if (is_object($migration) && method_exists($migration, 'up')) {
            $this->invokeObjectMigration($migration, $file);
            return;
        }

        if (is_array($migration)) {
            if (isset($migration['up']) && is_callable($migration['up'])) {
                $this->invokeCallable($migration['up']);
                return;
            }

            foreach ($migration as $statement) {
                if (!is_string($statement) || trim($statement) === '') {
                    throw new RuntimeException('Bad migration statement in file: ' . $file);
                }

                $this->execSql($statement);
            }
            return;
        }

        throw new RuntimeException('Bad migration file: ' . $file);
    }

    /**
     * Invoke a function/closure migration with the expected argument count.
     */
    private function invokeCallable(callable $callable): void
    {
        $closure = Closure::fromCallable($callable);
        $reflection = new ReflectionFunction($closure);
        $required = $reflection->getNumberOfRequiredParameters();

        if ($required >= 2) {
            $callable($this->pdo, $this->driver());
            return;
        }

        $callable($this->pdo);
    }

    /**
     * Invoke an object migration that exposes an up() method.
     */
    private function invokeObjectMigration(object $migration, string $file): void
    {
        $method = new ReflectionMethod($migration, 'up');
        $required = $method->getNumberOfRequiredParameters();

        if ($required > 2) {
            throw new RuntimeException('Migration up() method requires too many arguments: ' . $file);
        }

        if ($required >= 2) {
            $migration->up($this->pdo, $this->driver());
            return;
        }

        $migration->up($this->pdo);
    }

    /**
     * Execute one SQL statement.
     */
    private function execSql(string $sql): void
    {
        $sql = trim($sql);
        if ($sql === '') {
            return;
        }

        $this->pdo->exec($sql);
    }

    /**
     * Create the migration tracking table if it does not already exist.
     */
    private function ensureMigrationTable(): void
    {
        if (!$this->tableExists('migrations')) {
            $this->pdo->exec(
                $this->driver() === 'sqlite'
                    ? 'CREATE TABLE IF NOT EXISTS migrations (migration VARCHAR(255) PRIMARY KEY, migrated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP)'
                    : 'CREATE TABLE IF NOT EXISTS migrations (migration VARCHAR(255) NOT NULL PRIMARY KEY, migrated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP)'
            );
        }

        $this->migrationColumn = $this->detectMigrationColumn();
        $this->timestampColumn = $this->detectTimestampColumn();
    }

    /**
     * Detect the column used to store migration names.
     */
    private function detectMigrationColumn(): string
    {
        $columns = $this->columns('migrations');
        foreach (['migration', 'filename', 'name', 'version'] as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return $candidate;
            }
        }

        throw new RuntimeException('Migration tracking table exists but no migration-name column was found.');
    }

    /**
     * Detect the timestamp column in the migration tracking table.
     */
    private function detectTimestampColumn(): ?string
    {
        $columns = $this->columns('migrations');
        foreach (['migrated_at', 'applied_at', 'executed_at', 'created_at', 'updated_at'] as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Determine whether a migration file has already been applied.
     */
    private function hasMigrationRun(string $migrationName): bool
    {
        $column = $this->migrationColumn ?? $this->detectMigrationColumn();
        $statement = $this->pdo->prepare('SELECT 1 FROM ' . $this->quoteIdentifier('migrations') . ' WHERE ' . $this->quoteIdentifier($column) . ' = :migration LIMIT 1');
        $statement->execute(['migration' => $migrationName]);

        return (bool) $statement->fetchColumn();
    }

    /**
     * Mark a migration file as applied.
     */
    private function markMigrationRun(string $migrationName): void
    {
        $migrationColumn = $this->migrationColumn ?? $this->detectMigrationColumn();
        $timestampColumn = $this->timestampColumn ?? $this->detectTimestampColumn();

        if ($timestampColumn !== null) {
            $statement = $this->pdo->prepare(
                'INSERT INTO ' . $this->quoteIdentifier('migrations')
                . ' (' . $this->quoteIdentifier($migrationColumn) . ', ' . $this->quoteIdentifier($timestampColumn) . ')'
                . ' VALUES (:migration, CURRENT_TIMESTAMP)'
            );
            $statement->execute(['migration' => $migrationName]);
            return;
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO ' . $this->quoteIdentifier('migrations')
            . ' (' . $this->quoteIdentifier($migrationColumn) . ') VALUES (:migration)'
        );
        $statement->execute(['migration' => $migrationName]);
    }

    /**
     * Return column names for a table.
     *
     * @return list<string>
     */
    private function columns(string $table): array
    {
        if ($this->driver() === 'sqlite') {
            $rows = $this->pdo->query('PRAGMA table_info(' . $this->quoteIdentifier($table) . ')')->fetchAll(PDO::FETCH_ASSOC);
            return array_values(array_map(static fn (array $row): string => (string) $row['name'], $rows));
        }

        $rows = $this->pdo->query('SHOW COLUMNS FROM ' . $this->quoteIdentifier($table))->fetchAll(PDO::FETCH_ASSOC);
        return array_values(array_map(static fn (array $row): string => (string) $row['Field'], $rows));
    }

    /**
     * Determine whether a table exists.
     *
     * BUG FIX: the MySQL branch previously ran `SHOW TABLES LIKE :table` as
     * a bound, prepared statement — see this class's own docblock above
     * for the full explanation of why this broke once real server-side
     * prepared statements were correctly enabled (MySQL's binary prepared-
     * statement protocol does not support parameter binding for SHOW
     * statements at all). Fixed by querying information_schema.TABLES
     * instead, a real SELECT that fully supports bound parameters.
     */
    private function tableExists(string $table): bool
    {
        if ($this->driver() === 'sqlite') {
            $statement = $this->pdo->prepare("SELECT name FROM sqlite_master WHERE type = 'table' AND name = :table");
            $statement->execute(['table' => $table]);
            return (bool) $statement->fetchColumn();
        }

        $statement = $this->pdo->prepare(
            'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table LIMIT 1'
        );
        $statement->execute(['table' => $table]);
        return (bool) $statement->fetchColumn();
    }

    /**
     * Quote a safe SQL identifier.
     */
    private function quoteIdentifier(string $identifier): string
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier)) {
            throw new RuntimeException('Unsafe SQL identifier: ' . $identifier);
        }

        return $this->driver() === 'sqlite' ? '"' . $identifier . '"' : '`' . $identifier . '`';
    }

    /**
     * Return the active PDO driver name.
     */
    private function driver(): string
    {
        return (string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }
}
