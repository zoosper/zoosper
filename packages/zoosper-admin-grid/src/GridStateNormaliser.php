<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDefinition;

/**
 * Validates persisted or submitted admin-grid state against the live grid
 * definition. Unknown contributed/retired fields are discarded rather than
 * leaking into queries, bookmarks or visible-column preferences.
 */
final readonly class GridStateNormaliser
{
    /**
     * @param array<string, mixed> $state
     * @return array{
     *   filters: array<string, string>,
     *   sort_by: string|null,
     *   sort_dir: 'asc'|'desc',
     *   page_size: int,
     *   visible_columns: list<string>
     * }
     */
    public function normalise(array $state, GridDefinition $definition): array
    {
        $filters = [];
        $submittedFilters = is_array($state['filters'] ?? null) ? $state['filters'] : [];
        foreach ($definition->filterKeys() as $key) {
            $value = trim((string) ($submittedFilters[$key] ?? ''));
            if ($value !== '') {
                $filters[$key] = $value;
            }
        }

        $sortBy = is_string($state['sort_by'] ?? null)
            && $definition->isSortable($state['sort_by'])
            ? $state['sort_by']
            : $definition->defaultSort;
        $sortDir = strtolower((string) ($state['sort_dir'] ?? $definition->defaultSortDir)) === 'asc'
            ? 'asc'
            : 'desc';
        $pageSize = max(5, min(200, (int) ($state['page_size'] ?? 20)));

        $submittedColumns = is_array($state['visible_columns'] ?? null)
            ? array_values(array_unique(array_map('strval', $state['visible_columns'])))
            : $definition->defaultVisibleColumnKeys();
        $allowedColumns = array_fill_keys($definition->allColumnKeys(), true);
        $visibleColumns = array_values(array_filter(
            $submittedColumns,
            static fn (string $key): bool => isset($allowedColumns[$key]),
        ));
        foreach ($definition->columns as $column) {
            if (!$column->toggleable && !in_array($column->key, $visibleColumns, true)) {
                $visibleColumns[] = $column->key;
            }
        }

        return [
            'filters' => $filters,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
            'page_size' => $pageSize,
            'visible_columns' => $visibleColumns,
        ];
    }

    /** @param array<string, mixed> $state */
    public function criteria(array $state, GridDefinition $definition): GridCriteria
    {
        $normalised = $this->normalise($state, $definition);
        $values = [
            ...$normalised['filters'],
            'sort' => $normalised['sort_by'],
            'dir' => $normalised['sort_dir'],
            'page_size' => $normalised['page_size'],
        ];

        return GridCriteria::fromValues($values, $definition);
    }
}
