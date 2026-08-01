<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDefinition;

final readonly class GridStateNormaliser
{
    /**
     * @param array<string, mixed> $state
     * @return array{
     *   filters: array<string, string>,
     *   sort_by: string|null,
     *   sort_dir: 'asc'|'desc',
     *   page_size: int,
     *   visible_columns: list<string>,
     *   column_order: list<string>
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
        $allowed = array_fill_keys($definition->allColumnKeys(), true);

        $submittedColumns = is_array($state['visible_columns'] ?? null)
            ? array_values(array_unique(array_map('strval', $state['visible_columns'])))
            : $definition->defaultVisibleColumnKeys();
        $visibleColumns = array_values(array_filter(
            $submittedColumns,
            static fn (string $key): bool => isset($allowed[$key]),
        ));
        foreach ($definition->columns as $column) {
            if (!$column->toggleable && !in_array($column->key, $visibleColumns, true)) {
                $visibleColumns[] = $column->key;
            }
        }

        $submittedOrder = is_array($state['column_order'] ?? null)
            ? array_values(array_unique(array_map('strval', $state['column_order'])))
            : $definition->allColumnKeys();
        $columnOrder = array_values(array_filter(
            $submittedOrder,
            static fn (string $key): bool => isset($allowed[$key]),
        ));
        foreach ($definition->allColumnKeys() as $key) {
            if (!in_array($key, $columnOrder, true)) {
                $columnOrder[] = $key;
            }
        }

        return [
            'filters' => $filters,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
            'page_size' => $pageSize,
            'visible_columns' => $visibleColumns,
            'column_order' => $columnOrder,
        ];
    }

    /** @param array<string, mixed> $state */
    public function criteria(array $state, GridDefinition $definition): GridCriteria
    {
        $normalised = $this->normalise($state, $definition);

        return GridCriteria::fromValues([
            ...$normalised['filters'],
            'sort' => $normalised['sort_by'],
            'dir' => $normalised['sort_dir'],
            'page_size' => $normalised['page_size'],
        ], $definition);
    }
}
