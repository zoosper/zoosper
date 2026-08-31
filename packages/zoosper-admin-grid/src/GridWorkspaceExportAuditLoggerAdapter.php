<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Adapts the host audit logger to the export-specific audit contract. */
final readonly class GridWorkspaceExportAuditLoggerAdapter implements GridWorkspaceExportAuditorInterface
{
    public const ACTION = 'admin_grid.export';

    public function __construct(private GridWorkspaceAuditLoggerInterface $logger)
    {
    }

    public function record(GridWorkspaceExportAudit $audit): void
    {
        $this->logger->logAction(self::ACTION, [
            'admin_user_id' => $audit->adminUserId,
            'grid_key' => $audit->gridKey,
            'filename' => $audit->filename,
            'exported_rows' => $audit->exportedRows,
            'truncated' => $audit->truncated,
            'filters' => $audit->filters,
            'visible_columns' => $audit->visibleColumns,
        ]);
    }
}











