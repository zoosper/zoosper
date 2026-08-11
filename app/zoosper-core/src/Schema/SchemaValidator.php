<?php

declare(strict_types=1);

namespace Zoosper\Core\Schema;

final readonly class SchemaValidator
{
    public function validate(SchemaRegistry $registry): SchemaValidationResult
    {
        $errors = [];
        $supportedTypes = ['integer', 'int', 'bigint', 'string', 'text', 'longtext', 'datetime', 'boolean', 'bool', 'json'];

        foreach ($registry->tables() as $table) {
            if ($table->name === '') {
                $errors[] = 'Table name cannot be empty.';
            }
            if ($table->columns === []) {
                $errors[] = 'Table ' . $table->name . ' must declare at least one column.';
            }

            foreach ($table->columns as $columnName => $definition) {
                if (!is_array($definition)) {
                    $errors[] = 'Column ' . $table->name . '.' . $columnName . ' must be an array.';
                    continue;
                }
                $type = (string) ($definition['type'] ?? 'string');
                if (!in_array($type, $supportedTypes, true)) {
                    $errors[] = 'Column ' . $table->name . '.' . $columnName . ' uses unsupported type: ' . $type;
                }
                if (($definition['primary'] ?? false) && ($definition['nullable'] ?? false)) {
                    $errors[] = 'Primary column ' . $table->name . '.' . $columnName . ' cannot be nullable.';
                }
            }

            foreach ($table->indexes as $indexName => $definition) {
                if (!is_array($definition)) {
                    $errors[] = 'Index ' . $table->name . '.' . $indexName . ' must be an array.';
                    continue;
                }
                $columns = $definition['columns'] ?? [];
                if (!is_array($columns) || $columns === []) {
                    $errors[] = 'Index ' . $table->name . '.' . $indexName . ' must declare columns.';
                    continue;
                }
                foreach ($columns as $column) {
                    if (!array_key_exists((string) $column, $table->columns)) {
                        $errors[] = 'Index ' . $table->name . '.' . $indexName . ' references missing column: ' . (string) $column;
                    }
                }
            }
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
}

