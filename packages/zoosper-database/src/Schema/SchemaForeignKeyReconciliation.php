<?php

declare(strict_types=1);

namespace Zoosper\Database\Schema;

/** One explicit reconciliation outcome for an existing table constraint. */
final readonly class SchemaForeignKeyReconciliation
{
    public const PRESENT = 'present';
    public const ADD = 'add';
    public const MISMATCH = 'mismatch';
    public const SQLITE_REBUILD_REQUIRED = 'sqlite_rebuild_required';

    public function __construct(
        public string $table,
        public SchemaForeignKey $expected,
        public string $status,
        public ?string $sql = null,
        public ?string $diagnostic = null,
    ) {
    }
}











