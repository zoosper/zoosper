<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Selects the real audit adapter when the host provides a logger. */
/**
 * Declarative runtime entry point discovered through module configuration.
 *
 * @psalm-api
 */
final class GridWorkspaceExportAuditorFactory
{
    public static function create(
        ?GridWorkspaceAuditLoggerInterface $logger,
    ): GridWorkspaceExportAuditorInterface {
        return $logger === null
            ? new NullGridWorkspaceExportAuditor()
            : new GridWorkspaceExportAuditLoggerAdapter($logger);
    }

    /** @psalm-suppress UnusedConstructor Static-only factory guard. */
    private function __construct()
    {
    }
}











