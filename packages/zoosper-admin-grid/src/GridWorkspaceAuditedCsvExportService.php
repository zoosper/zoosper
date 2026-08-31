<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Exports a resolved view and records the completed, bounded outcome. */
final readonly class GridWorkspaceAuditedCsvExportService
{
    public function __construct(
        private GridWorkspaceCsvExportService $exports,
        private GridWorkspaceExportAuditorInterface $auditor,
    ) {
    }

    /** @param iterable<array<string, mixed>> $rows */
    public function export(
        int $adminUserId,
        string $gridKey,
        GridViewState $state,
        iterable $rows,
        string $filename,
    ): GridWorkspaceExportResult {
        $result = $this->exports->export($state, $rows, $filename);
        $this->auditor->record(new GridWorkspaceExportAudit(
            adminUserId: $adminUserId,
            gridKey: $gridKey,
            filename: $result->filename,
            exportedRows: $result->exportedRows,
            truncated: $result->truncated,
            filters: $state->criteria->filters,
            visibleColumns: $state->visibleColumns,
        ));

        return $result;
    }
}











