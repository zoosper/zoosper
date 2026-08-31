<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Normalised pagination metadata used by workspace navigation. */
final readonly class GridWorkspacePagination
{
    public function __construct(
        public int $currentPage,
        public int $totalPages,
        public int $totalItems,
    ) {
        if ($currentPage < 1 || $totalPages < 1 || $totalItems < 0) {
            throw new \InvalidArgumentException('Grid pagination metadata is invalid.');
        }
        if ($currentPage > $totalPages) {
            throw new \InvalidArgumentException('Grid current page cannot exceed total pages.');
        }
    }
}











