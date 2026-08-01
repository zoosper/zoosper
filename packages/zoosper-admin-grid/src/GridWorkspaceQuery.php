<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/**
 * Serialises resolved Grid state into safe GET query parameters for sorting,
 * pagination, bookmark switching and export links.
 */
final readonly class GridWorkspaceQuery
{
    /** @return array<string, mixed> */
    public function parameters(
        GridViewState $state,
        ?int $page = null,
        ?string $sortBy = null,
        ?string $sortDir = null,
    ): array {
        $parameters = [];

        foreach ($state->criteria->filters as $key => $value) {
            if ($value !== '' && $value !== []) {
                $parameters[$key] = $value;
            }
        }

        $parameters['sort'] = $sortBy ?? $state->criteria->sortBy;
        $parameters['dir'] = $sortDir ?? $state->criteria->sortDir;
        $parameters['page_size'] = $state->criteria->pager->pageSize;

        if ($page !== null && $page > 1) {
            $parameters['page'] = $page;
        }
        if ($state->activeBookmarkId !== null) {
            $parameters['bookmark_id'] = $state->activeBookmarkId;
        }
        if ($state->visibleColumns !== []) {
            $parameters['visible_columns'] = $state->visibleColumns;
        }
        if ($state->columnOrder !== []) {
            $parameters['column_order'] = $state->columnOrder;
        }

        return array_filter(
            $parameters,
            static fn (mixed $value): bool => $value !== null && $value !== '',
        );
    }

    public function url(
        string $localPath,
        GridViewState $state,
        ?int $page = null,
        ?string $sortBy = null,
        ?string $sortDir = null,
    ): string {
        $this->assertLocalPath($localPath);
        $query = http_build_query(
            $this->parameters($state, $page, $sortBy, $sortDir),
            '',
            '&',
            PHP_QUERY_RFC3986,
        );

        return $query === '' ? $localPath : $localPath . '?' . $query;
    }

    private function assertLocalPath(string $path): void
    {
        if ($path === '' || $path[0] !== '/' || str_contains($path, '://')) {
            throw new \InvalidArgumentException(
                'Grid workspace URLs must use an application-local path.',
            );
        }
    }
}
