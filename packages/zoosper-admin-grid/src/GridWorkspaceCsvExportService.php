<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

use Zoosper\Grid\GridCsvExporter;

/**
 * Exports the resolved Grid view only, applying its visible columns and order.
 * Feature controllers remain responsible for permission checks and audit logs.
 */
final readonly class GridWorkspaceCsvExportService
{
    public function __construct(
        private GridCsvExporter $exporter,
        private GridWorkspaceExportPolicy $policy = new GridWorkspaceExportPolicy(),
    ) {
    }

    /** @param iterable<array<string, mixed>> $rows */
    public function export(
        GridViewState $state,
        iterable $rows,
        string $filename,
    ): GridWorkspaceExportResult {
        $limited = [];
        $truncated = false;
        foreach ($rows as $row) {
            if (count($limited) >= $this->policy->maximumRows) {
                $truncated = true;
                break;
            }
            $limited[] = $row;
        }

        return new GridWorkspaceExportResult(
            filename: $this->normaliseFilename($filename),
            csv: $this->exporter->export(
                $state->definition,
                $limited,
                $state->visibleColumns,
            ),
            exportedRows: count($limited),
            truncated: $truncated,
        );
    }

    private function normaliseFilename(string $filename): string
    {
        $filename = strtolower(trim($filename));
        $filename = preg_replace('/[^a-z0-9._-]+/', '-', $filename) ?? '';
        $filename = trim($filename, '.-_');
        if ($filename === '') {
            $filename = 'grid-export';
        }
        if (!str_ends_with($filename, '.csv')) {
            $filename .= '.csv';
        }

        return $filename;
    }
}
