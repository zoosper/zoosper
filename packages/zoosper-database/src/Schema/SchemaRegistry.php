<?php

declare(strict_types=1);

namespace Zoosper\Database\Schema;

final class SchemaRegistry
{
    /** @var array<string, SchemaTable> */
    private array $tables = [];

    public function addTable(SchemaTable $table): void
    {
        if (isset($this->tables[$table->name])) {
            $existing = $this->tables[$table->name];
            $this->tables[$table->name] = new SchemaTable(
                name: $table->name,
                columns: array_replace($existing->columns, $table->columns),
                indexes: array_replace($existing->indexes, $table->indexes),
                foreignKeys: array_replace($existing->foreignKeys, $table->foreignKeys),
            );

            return;
        }

        $this->tables[$table->name] = $table;
    }

    /** @return array<string, SchemaTable> */
    public function tables(): array
    {
        return $this->tables;
    }
}











