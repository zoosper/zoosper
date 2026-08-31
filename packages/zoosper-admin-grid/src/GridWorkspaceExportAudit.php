<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Immutable audit context for one completed Grid export. */
final readonly class GridWorkspaceExportAudit
{
    /** @param array<string, mixed> $filters */
    public function __construct(
        public int $adminUserId,
        public string $gridKey,
        public string $filename,
        public int $exportedRows,
        public bool $truncated,
        public array $filters,
        public array $visibleColumns,
    ) {
        if ($adminUserId <= 0) {
            throw new \InvalidArgumentException('Export audit requires a positive admin user ID.');
        }
        if (trim($gridKey) === '') {
            throw new \InvalidArgumentException('Export audit requires a Grid key.');
        }
    }
}











