<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\BulkAction;

use Zoosper\Grid\BulkAction\GridBulkActionDispatcher;
use Zoosper\Grid\BulkAction\GridBulkActionExecutorRegistry;
use Zoosper\Grid\BulkAction\GridBulkActionRegistry;

/** Builds the protected coordinator from shared registries and host bindings. */
final readonly class GridBulkHttpCoordinatorFactory
{
    public function create(
        GridBulkActionRegistry $definitions,
        GridBulkActionExecutorRegistry $executors,
        GridBulkHostBindings $bindings,
    ): GridBulkHttpCoordinator {
        return new GridBulkHttpCoordinator(
            parser: new GridBulkHttpRequestParser(),
            definitions: $definitions,
            dispatcher: new GridBulkActionDispatcher($definitions, $executors),
            csrf: new GridBulkCsrfVerifier($bindings->csrfValidator),
            permissions: new GridBulkPermissionChecker($bindings->permissionChecker),
            audit: new GridBulkAuditGuard($bindings->auditReadiness),
            confirmation: new GridBulkConfirmationGuard(),
        );
    }
}
