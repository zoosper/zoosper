<?php

declare(strict_types=1);

namespace Zoosper\Database\Schema;

/** Normalised foreign-key metadata read from a live database. */
final readonly class SchemaForeignKeyState
{
    /** @param non-empty-list<string> $columns @param non-empty-list<string> $referencedColumns */
    public function __construct(
        public string $name,
        public array $columns,
        public string $referencedTable,
        public array $referencedColumns,
        public string $onDelete,
        public string $onUpdate,
    ) {
    }

    public function matches(SchemaForeignKey $expected): bool
    {
        return $this->columns === $expected->columns
            && $this->referencedTable === $expected->referencedTable
            && $this->referencedColumns === $expected->referencedColumns
            && strtoupper($this->onDelete) === $expected->onDelete
            && strtoupper($this->onUpdate) === $expected->onUpdate;
    }
}











