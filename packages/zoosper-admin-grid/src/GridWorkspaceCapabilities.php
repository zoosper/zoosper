<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Feature contract exposed to each grid-page integration. */
final readonly class GridWorkspaceCapabilities
{
    public function __construct(
        public bool $filters = true,
        public bool $columnVisibility = true,
        public bool $columnOrdering = true,
        public bool $bookmarks = true,
        public bool $csvExport = true,
    ) {
    }
}











