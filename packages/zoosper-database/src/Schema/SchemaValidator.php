<?php

declare(strict_types=1);

namespace Zoosper\Database\Schema;

final readonly class SchemaValidator
{
    public function validate(SchemaRegistry $registry): SchemaValidationResult
    {
        $errors = [];
        foreach ($registry->tables() as $table) {
            if ($table->name === '') {
                $errors[] = 'Table name cannot be empty.';
            }
            if ($table->columns === []) {
                $errors[] = 'Table ' . $table->name . ' must declare at least one column.';
            }

            array_push($errors, ...$this->validateColumns($table->name, $table->columns));
            array_push($errors, ...$this->validateIndexes($table->name, $table->columns, $table->indexes));
        }
        foreach ($registry->tables() as $table) {
            foreach ($table->foreignKeys as $name => $foreignKey) {
                foreach ($foreignKey->columns as $column) {
                    if (!array_key_exists($column, $table->columns)) {
                        $errors[] = sprintf('Table %s foreign key %s references missing local column %s.', $table->name, $name, $column);
                    }
                }

                $referenced = $registry->tables()[$foreignKey->referencedTable] ?? null;
                if ($referenced === null) {
                    $errors[] = sprintf('Table %s foreign key %s references missing table %s.', $table->name, $name, $foreignKey->referencedTable);
                    continue;
                }

                foreach ($foreignKey->referencedColumns as $column) {
                    if (!array_key_exists($column, $referenced->columns)) {
                        $errors[] = sprintf('Table %s foreign key %s references missing column %s.%s.', $table->name, $name, $foreignKey->referencedTable, $column);
                    }
                }

                if ($foreignKey->onDelete === SchemaForeignKey::ACTION_SET_NULL) {
                    foreach ($foreignKey->columns as $column) {
                        if (!(bool) ($table->columns[$column]['nullable'] ?? false)) {
                            $errors[] = sprintf('Table %s foreign key %s uses SET NULL but local column %s is not nullable.', $table->name, $name, $column);
                        }
                    }
                }
            }
        }


        return new SchemaValidationResult($errors);
    }

    /**
     * @param array<string, mixed> $columns
     * @return list<string>
     */
    private function validateColumns(string $table, array $columns): array
    {
        $errors = [];
        $supportedTypes = ['integer', 'int', 'bigint', 'string', 'text', 'longtext', 'datetime', 'boolean', 'bool', 'json'];
        foreach ($columns as $columnName => $definition) {
            if (!is_array($definition)) {
                $errors[] = 'Column ' . $table . '.' . $columnName . ' must be an array.';
                continue;
            }
            $type = (string) ($definition['type'] ?? 'string');
            if (!in_array($type, $supportedTypes, true)) {
                $errors[] = 'Column ' . $table . '.' . $columnName . ' uses unsupported type: ' . $type;
            }
            if ((bool) ($definition['primary'] ?? false) && (bool) ($definition['nullable'] ?? false)) {
                $errors[] = 'Primary column ' . $table . '.' . $columnName . ' cannot be nullable.';
            }
        }
        return $errors;
    }

    /**
     * @param array<string, mixed> $columns
     * @param array<string, mixed> $indexes
     * @return list<string>
     */
    private function validateIndexes(string $table, array $columns, array $indexes): array
    {
        $errors = [];
        foreach ($indexes as $indexName => $definition) {
            if (!is_array($definition)) {
                $errors[] = 'Index ' . $table . '.' . $indexName . ' must be an array.';
                continue;
            }
            $indexColumns = $definition['columns'] ?? [];
            if (!is_array($indexColumns) || $indexColumns === []) {
                $errors[] = 'Index ' . $table . '.' . $indexName . ' must declare columns.';
                continue;
            }
            foreach ($indexColumns as $column) {
                if (!array_key_exists((string) $column, $columns)) {
                    $errors[] = 'Index ' . $table . '.' . $indexName . ' references missing column: ' . (string) $column;
                }
            }
        }
        return $errors;
    }
}












