<?php

declare(strict_types=1);

namespace Zoosper\Grid\BulkAction;

/** Trusted execution context assembled by the authenticated HTTP integration. */
final readonly class GridBulkExecutionContext
{
    public function __construct(public GridBulkActor $actor)
    {
    }
}











