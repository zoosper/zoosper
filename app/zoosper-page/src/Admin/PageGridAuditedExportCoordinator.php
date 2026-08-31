<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\AdminGrid\GridViewState;
use Zoosper\AdminGrid\GridWorkspaceAuditedCsvExportService;
use Zoosper\AdminGrid\GridWorkspaceExportResult;

/** Pages export boundary with fixed Grid and filename identity. */
final readonly class PageGridAuditedExportCoordinator
{
    public function __construct(private GridWorkspaceAuditedCsvExportService $exports)
    {
    }

    /** @param iterable<array<string, mixed>> $rows */
    public function export(
        int $authenticatedAdminUserId,
        GridViewState $state,
        iterable $rows,
    ): GridWorkspaceExportResult {
        return $this->exports->export(
            adminUserId: $authenticatedAdminUserId,
            gridKey: PageGridWorkspace::GRID_KEY,
            state: $state,
            rows: $rows,
            filename: PageGridExportCoordinator::FILENAME,
        );
    }
}










