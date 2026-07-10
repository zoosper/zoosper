<?php

declare(strict_types=1);

namespace Zoosper\Core\Database;

use PDO;
use RuntimeException;
use Zoosper\Core\Module\ModuleRegistry;

/**
 * Applies module-owned declarative database schema files.
 *
 * The applier scans every enabled module for `config/db_schema.php` and creates
 * missing tables/indexes in an idempotent way. It intentionally focuses on safe
 * additive schema creation for early Zoosper phases and does not drop or alter
 * existing columns, because destructive schema changes need a separate audited
 * migration process.
 */
final readonly class DeclarativeSchemaApplier
{
    public function __construct(
        private PDO $pdo,
        private ModuleRegistry $modules,
    ) {
    }

    /**
     * Apply all enabled module-owned declarative schema files.
     *
     * @return list<string> Human-readable checked operations.
     */
    public function applyAll(): array
    {
        $messages = [];

        foreach ($this->modules->enabledModules() as $module) {
            $schemaFile = $module->configPath('db_schema.php');
            if (!is_file($schemaFile)) {
                continue;
            }

            $schema = require $schemaFile;
            if (!is_array($schema)) {
                throw new RuntimeException('Declarative schema file must return an array: ' . $schemaFile);
            }

            foreach (($schema['tables'] ?? []) as $tableName => $tableDefinition) {
                if (!is_string($tableName) || !is_array($tableDefinition)) {
                    throw new RuntimeException('Invalid table definition in schema file: ' . $schemaFile);
                }

                $this->createTableIfMissing($tableName, $tableDefinition);
                $messages[] = 'checked table ' . $tableName . ' from ' . $module->name;

                foreach (($tableDefinition['indexes'] ?? []) as $indexName => $indexDefinition) {
                    if (!is_string($indexName) || !is_array($indexDefinition)) {
                        throw new RuntimeException('Invalid index definition for table ' . $tableName . ' in: ' . $schemaFile);
                    }

                    $this->createIndexIfMissing($tableName, $indexName, $indexDefinition);
                    $messages[] = 'checked index ' . $indexName . ' on ' . $tableName;
                }
            }
        }

        return $messages;
    }

    /**
     * Create a table if it is not present.
     *
     * @param array<string, mixed> $definition
     */
    private function createTableIfMissing(string $tableName, array $definition): void
    {
        if ($this->tableExists($tableName)) {
            return;
        }

        $columns = [];
        foreach (($definition['columns'] ?? []) as $columnName => $columnDefinition) {
            if (!is_string($columnName) || !is_array($columnDefinition)) {
                throw new RuntimeException('Invalid column definition for table: ' . $tableName);
            }

            $columns[] = $this->columnSql($columnName, $columnDefinition);
        }

        if ($columns === []) {
            throw new RuntimeException('Cannot create table without columns: ' . $tableName);
        }

        $sql = 'CREATE TABLE IF NOT EXISTS ' . $this->quoteIdentifier($tableName) . ' (' . implode(', ', $columns) . ')';
        $this->pdo->exec($sql);
    }

    /**
     * Create an index if it is not already present.
     *
     * MySQL/MariaDB do not consistently support `CREATE INDEX IF NOT EXISTS`,
     * so this method performs an explicit metadata check first and then issues a
     * plain `CREATE INDEX` statement. SQLite can support `IF NOT EXISTS`, but we
     * use the same pre-check approach for consistent behaviour.
     *
     * @param array<string, mixed> $definition
     */
    private function createIndexIfMissing(string $tableName, string $indexName, array $definition): void
    {
        if ($this->indexExists($tableName, $indexName)) {
            return;
        }

        $columns = $definition['columns'] ?? [];
        if (!is_array($columns) || $columns === []) {
            throw new RuntimeException('Index must declare columns: ' . $indexName);
        }

        $unique = (bool) ($definition['unique'] ?? false);
        $columnSql = implode(', ', array_map(fn (string $column): string => $this->quoteIdentifier($column), $columns));
        $sql = 'CREATE ' . ($unique ? 'UNIQUE ' : '') . 'INDEX '
            . $this->quoteIdentifier($indexName) . ' ON ' . $this->quoteIdentifier($tableName) . ' (' . $columnSql . ')';

        $this->pdo->exec($sql);
    }

    /**
     * Build SQL for a single column definition.
     *
     * @param array<string, mixed> $definition
     */
    private function columnSql(string $columnName, array $definition): string
    {
        $isPrimary = (bool) ($definition['primary'] ?? false);
        $autoIncrement = (bool) ($definition['auto_increment'] ?? false);
        $nullable = (bool) ($definition['nullable'] ?? false);
        $type = (string) ($definition['type'] ?? 'string');

        if ($this->driver() === 'sqlite' && $isPrimary && $autoIncrement) {
            return $this->quoteIdentifier($columnName) . ' INTEGER PRIMARY KEY AUTOINCREMENT';
        }

        $sql = $this->quoteIdentifier($columnName) . ' ' . $this->typeSql($type, $definition);

        if ($isPrimary) {
            $sql .= ' PRIMARY KEY';
        }

        if ($autoIncrement && $this->driver() !== 'sqlite') {
            $sql .= ' AUTO_INCREMENT';
        }

        if (!$nullable && !$isPrimary) {
            $sql .= ' NOT NULL';
        }

        if (array_key_exists('default', $definition)) {
            $sql .= ' DEFAULT ' . $this->defaultSql($definition['default']);
        }

        return $sql;
    }

    /**
     * Convert a portable schema type into SQL for the current driver.
     *
     * @param array<string, mixed> $definition
     */
    private function typeSql(string $type, array $definition): string
    {
        return match ($type) {
            'integer' => $this->driver() === 'sqlite' ? 'INTEGER' : 'INT',
            'text' => 'TEXT',
            'datetime' => 'DATETIME',
            'string' => 'VARCHAR(' . (int) ($definition['length'] ?? 255) . ')',
            default => throw new RuntimeException('Unsupported declarative schema column type: ' . $type),
        };
    }

    /**
     * Render a default value safely for schema SQL.
     */
    private function defaultSql(mixed $value): string
    {
        if ($value === 'CURRENT_TIMESTAMP') {
            return 'CURRENT_TIMESTAMP';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return $this->pdo->quote((string) $value);
    }

    /**
     * Determine whether a table exists.
     */
    private function tableExists(string $tableName): bool
    {
        if ($this->driver() === 'sqlite') {
            $statement = $this->pdo->prepare("SELECT name FROM sqlite_master WHERE type = 'table' AND name = :table");
            $statement->execute(['table' => $tableName]);
            return (bool) $statement->fetchColumn();
        }

        $statement = $this->pdo->prepare('SHOW TABLES LIKE :table');
        $statement->execute(['table' => $tableName]);
        return (bool) $statement->fetchColumn();
    }

    /**
     * Determine whether an index exists.
     */
    private function indexExists(string $tableName, string $indexName): bool
    {
        if ($this->driver() === 'sqlite') {
            $statement = $this->pdo->prepare("SELECT name FROM sqlite_master WHERE type = 'index' AND name = :index");
            $statement->execute(['index' => $indexName]);
            return (bool) $statement->fetchColumn();
        }

        $statement = $this->pdo->prepare('SHOW INDEX FROM ' . $this->quoteIdentifier($tableName) . ' WHERE Key_name = :index');
        $statement->execute(['index' => $indexName]);
        return (bool) $statement->fetchColumn();
    }

    /**
     * Quote an SQL identifier for the current database driver.
     */
    private function quoteIdentifier(string $identifier): string
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier)) {
            throw new RuntimeException('Unsafe SQL identifier: ' . $identifier);
        }

        return $this->driver() === 'sqlite' ? '"' . $identifier . '"' : '`' . $identifier . '`';
    }

    /**
     * Current PDO driver name.
     */
    private function driver(): string
    {
        return (string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }
}
