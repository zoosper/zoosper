<?php

declare(strict_types=1);

namespace Zoosper\Core\Schema;

use RuntimeException;

/**
 * CORRECTNESS FIX (confirmed 2026-07-30, external reviewer pass):
 * createTableSql() previously appended `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4`
 * with NO explicit COLLATE clause on MySQL/MariaDB, relying entirely on
 * whatever the connected server's own default utf8mb4 collation happens to
 * be. MariaDB's default collation for utf8mb4 has genuinely changed across
 * major versions (e.g. older defaults vs. utf8mb4_uca1400_ai_ci-family
 * defaults on newer releases). The exact same declarative schema applied
 * against dev/staging/production servers running different MariaDB point
 * releases could therefore silently produce tables with DIFFERENT
 * collations for the same columns — which then causes subtly different
 * string comparison, sort order, and (for unique indexes) even different
 * uniqueness behaviour between environments running "the same" schema.
 *
 * Fixed by explicitly pinning `COLLATE=utf8mb4_unicode_ci` alongside the
 * existing CHARSET clause, so every environment gets an identical,
 * predictable collation regardless of the connected server's own default.
 * utf8mb4_unicode_ci is chosen as a broadly-compatible, linguistically-aware
 * collation supported across the MariaDB/MySQL versions this project
 * already targets — not the newer, MariaDB-only uca1400 family, to avoid
 * introducing a version floor as a side effect of this fix.
 *
 * SQLite's branch is completely unaffected — collation pinning in this
 * sense is a MySQL/MariaDB-specific concept that does not apply to SQLite.
 */
final readonly class SchemaSqlBuilder
{
    public function __construct(private string $driver)
    {
    }

    public function createTableSql(SchemaTable $table): string
    {
        $columns = [];
        foreach ($table->columns as $name => $definition) {
            $columns[] = $this->columnSql((string) $name, $definition, true);
        }
        foreach ($table->foreignKeys as $foreignKey) {
            $columns[] = $this->foreignKeySql($foreignKey);
        }
        return sprintf('CREATE TABLE IF NOT EXISTS %s (%s)%s', $table->name, implode(', ', $columns), $this->driver === 'mysql' ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : '');
    }

    public function addForeignKeySql(string $table, SchemaForeignKey $foreignKey): string
    {
        if ($this->driver === 'sqlite') {
            throw new RuntimeException('SQLite foreign keys require an explicit table rebuild migration.');
        }

        return sprintf(
            'ALTER TABLE %s ADD %s',
            $this->identifier($table),
            $this->foreignKeySql($foreignKey),
        );
    }

    private function foreignKeySql(SchemaForeignKey $foreignKey): string
    {
        return sprintf(
            'CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s (%s) ON DELETE %s ON UPDATE %s',
            $this->identifier($foreignKey->name),
            implode(', ', array_map($this->identifier(...), $foreignKey->columns)),
            $this->identifier($foreignKey->referencedTable),
            implode(', ', array_map($this->identifier(...), $foreignKey->referencedColumns)),
            $foreignKey->onDelete,
            $foreignKey->onUpdate,
        );
    }

    private function identifier(string $identifier): string
    {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier) !== 1) {
            throw new RuntimeException('Unsafe schema identifier: ' . $identifier);
        }

        return $identifier;
    }

    /** @param array<string, mixed> $definition */
    public function addColumnSql(string $table, string $column, array $definition): string
    {
        return sprintf('ALTER TABLE %s ADD COLUMN %s', $table, $this->columnSql($column, $definition, false));
    }

    /** @param array<string, mixed> $definition */
    public function createIndexSql(string $table, string $indexName, array $definition): string
    {
        $columns = $definition['columns'] ?? [];
        if (!is_array($columns) || $columns === []) {
            throw new RuntimeException('Index must define at least one column: ' . $indexName);
        }
        $unique = (bool) ($definition['unique'] ?? false);
        return sprintf('CREATE %sINDEX %s ON %s (%s)', $unique ? 'UNIQUE ' : '', $indexName, $table, implode(', ', $columns));
    }

    /** @param array<string, mixed> $definition */
    private function columnSql(string $name, array $definition, bool $allowPrimary): string
    {
        $type = (string) ($definition['type'] ?? 'string');
        $nullable = (bool) ($definition['nullable'] ?? false);
        $primary = $allowPrimary && (bool) ($definition['primary'] ?? false);
        $autoIncrement = (bool) ($definition['auto_increment'] ?? false);
        $default = $definition['default'] ?? null;

        if ($primary && $autoIncrement) {
            return $this->driver === 'mysql'
                ? sprintf('%s INT AUTO_INCREMENT PRIMARY KEY', $name)
                : sprintf('%s INTEGER PRIMARY KEY AUTOINCREMENT', $name);
        }

        $sql = $name . ' ' . $this->typeSql($type, $definition);
        if (!$nullable) {
            $sql .= ' NOT NULL';
        } else {
            $sql .= ' NULL';
        }
        if ($default !== null) {
            $sql .= ' DEFAULT ' . $this->defaultSql($default);
        }
        if ($primary) {
            $sql .= ' PRIMARY KEY';
        }
        return $sql;
    }

    /** @param array<string, mixed> $definition */
    private function typeSql(string $type, array $definition): string
    {
        return match ($type) {
            'integer', 'int' => $this->driver === 'mysql' ? 'INT' : 'INTEGER',
            'bigint' => $this->driver === 'mysql' ? 'BIGINT' : 'INTEGER',
            'text' => 'TEXT',
            'longtext' => $this->driver === 'mysql' ? 'LONGTEXT' : 'TEXT',
            'datetime' => $this->driver === 'mysql' ? 'DATETIME' : 'TEXT',
            'boolean', 'bool' => $this->driver === 'mysql' ? 'TINYINT(1)' : 'INTEGER',
            'json' => $this->driver === 'mysql' ? 'LONGTEXT' : 'TEXT',
            'string' => $this->driver === 'mysql' ? 'VARCHAR(' . (int) ($definition['length'] ?? 255) . ')' : 'TEXT',
            default => throw new RuntimeException('Unsupported schema type: ' . $type),
        };
    }

    private function defaultSql(mixed $value): string
    {
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        $string = (string) $value;
        if (strtoupper($string) === 'CURRENT_TIMESTAMP') {
            return 'CURRENT_TIMESTAMP';
        }

        return "'" . str_replace("'", "''", $string) . "'";
    }
}

