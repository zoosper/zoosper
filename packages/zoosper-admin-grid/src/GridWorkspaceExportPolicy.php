<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Defines the hard server-side row ceiling for one Grid CSV export. */
final readonly class GridWorkspaceExportPolicy
{
    public function __construct(public int $maximumRows = 5000)
    {
        if ($this->maximumRows < 1 || $this->maximumRows > 50000) {
            throw new \InvalidArgumentException(
                'Grid export maximum rows must be between 1 and 50000.',
            );
        }
    }
}











