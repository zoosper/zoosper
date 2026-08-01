<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Optional host integration for recording sensitive Grid exports. */
interface GridWorkspaceExportAuditorInterface
{
    public function record(GridWorkspaceExportAudit $audit): void;
}
