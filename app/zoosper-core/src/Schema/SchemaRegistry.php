<?php

declare(strict_types=1);

namespace Zoosper\Core\Schema;

final class SchemaRegistry
{
    /** @var array<string, SchemaTable> */
    private array $tables = [];

    public function addTable(SchemaTable $table): void
    {
        if (isset($this->tables[$table->name])) {
            $this->tables[$table->name] = new SchemaTable(
                name: $table->name,
                columns: array_replace($this->tables[$table->name]->columns, $table->columns),
                indexes: array_replace($this->tables[$table->name]->indexes, $table->indexes),
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
