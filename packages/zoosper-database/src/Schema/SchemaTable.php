<?php

declare(strict_types=1);

namespace Zoosper\Database\Schema;

final readonly class SchemaTable
{
    /**
     * @param array<string, array<string, mixed>> $columns
     * @param array<string, array<string, mixed>> $indexes
     */
    public function __construct(
        public string $name,
        public array $columns,
        public array $indexes = [],
        /** @var array<string, SchemaForeignKey> */
        public array $foreignKeys = [],
    ) {
    }
}











