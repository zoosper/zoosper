<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\AdminGrid\GridViewState;
use Zoosper\AdminGrid\GridWorkspaceNavigation;

/** Builds Page navigation from the same resolved state used by rows and export. */
final readonly class PageGridNavigationBuilder
{
    public function __construct(private PageGridLinks $links)
    {
    }

    public function build(
        GridViewState $state,
        int $currentPage,
        int $totalPages,
    ): GridWorkspaceNavigation {
        $currentPage = max(1, $currentPage);
        $totalPages = max(1, $totalPages);
        $sortUrls = [];

        foreach ($state->definition->columns as $column) {
            if (!$column->sortable) {
                continue;
            }
            $nextDirection = $state->criteria->sortBy === $column->key
                && $state->criteria->sortDir === 'asc'
                ? 'desc'
                : 'asc';
            $sortUrls[$column->key] = $this->links->sort(
                $state,
                $column->key,
                $nextDirection,
            );
        }

        return new GridWorkspaceNavigation(
            previousUrl: $currentPage > 1
                ? $this->links->page($state, $currentPage - 1)
                : null,
            nextUrl: $currentPage < $totalPages
                ? $this->links->page($state, $currentPage + 1)
                : null,
            sortUrls: $sortUrls,
            exportUrl: $this->links->export($state),
        );
    }
}
