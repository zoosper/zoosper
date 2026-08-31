<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\BulkAction;

use Zoosper\Grid\BulkAction\GridBulkActionDefinition;
use Zoosper\Grid\BulkAction\GridBulkSelection;

/** Ensures required audit infrastructure is available before execution. */
interface GridBulkAuditGuardInterface
{
    public function assertAvailable(
        GridBulkActionDefinition $definition,
        GridBulkSelection $selection,
    ): void;
}











