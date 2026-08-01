<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Selects the real audit adapter when the host provides a logger. */
final class GridWorkspaceExportAuditorFactory
{
    public static function create(
        ?GridWorkspaceAuditLoggerInterface $logger,
    ): GridWorkspaceExportAuditorInterface {
        return $logger === null
            ? new NullGridWorkspaceExportAuditor()
            : new GridWorkspaceExportAuditLoggerAdapter($logger);
    }

    private function __construct()
    {
    }
}
