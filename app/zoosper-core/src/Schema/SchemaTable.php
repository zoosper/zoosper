<?php

declare(strict_types=1);

namespace Zoosper\Core\Schema;

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
    ) {
    }
}
