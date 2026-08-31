<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Safe default when the host does not install an export-audit provider. */
final readonly class NullGridWorkspaceExportAuditor implements GridWorkspaceExportAuditorInterface
{
    public function record(GridWorkspaceExportAudit $audit): void
    {
    }
}











