<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\BulkAction;

/** Adapts the host permission service for one authenticated administrator. */
interface GridBulkPermissionCheckerInterface
{
    public function isAllowed(string $permission): bool;
}











