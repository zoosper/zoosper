<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDefinition;

/**
 * Immutable resolved state for one admin user and one grid.
 */
final readonly class GridViewState
{
    /**
     * @param list<string> $visibleColumns
     * @param list<array{id: int, name: string, state: array<string, mixed>, is_default: bool}> $bookmarks
     */
    public function __construct(
        public GridDefinition $definition,
        public GridCriteria $criteria,
        public array $visibleColumns,
        public array $bookmarks,
        public ?int $activeBookmarkId = null,
    ) {
    }
}
