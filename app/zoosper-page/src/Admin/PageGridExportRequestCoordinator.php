<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\AdminGrid\GridWorkspaceExportResult;
use Zoosper\AdminGrid\GridWorkspaceRequest;

/**
 * Resolves the authenticated administrator's current Pages view and exports the
 * rows returned by the feature-owned export data source.
 */
final readonly class PageGridExportRequestCoordinator
{
    public function __construct(
        private PageGridHttpCoordinator $workspace,
        private PageGridExportDataSourceInterface $rows,
        private PageGridAuditedExportCoordinator $exports,
    ) {
    }

    public function export(
        int $authenticatedAdminUserId,
        GridWorkspaceRequest $request,
    ): GridWorkspaceExportResult {
        $resolved = $this->workspace->view($authenticatedAdminUserId, $request);
        $state = $resolved['state'];

        return $this->exports->export(
            authenticatedAdminUserId: $authenticatedAdminUserId,
            state: $state,
            rows: $this->rows->exportRows($state->criteria),
        );
    }
}










