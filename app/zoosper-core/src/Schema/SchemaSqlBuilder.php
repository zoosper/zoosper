<?php

declare(strict_types=1);

namespace Zoosper\Core\Schema;

use RuntimeException;

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
        return sprintf('CREATE TABLE IF NOT EXISTS %s (%s)%s', $table->name, implode(', ', $columns), $this->driver === 'mysql' ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4' : '');
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
