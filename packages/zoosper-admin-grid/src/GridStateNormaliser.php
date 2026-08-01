<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDefinition;
use Zoosper\Grid\GridFilterValue;

final readonly class GridStateNormaliser
{
    /** @param array<string, mixed> $state @return array<string, mixed> */
    public function normalise(array $state, GridDefinition $definition): array
    {
        $filters = [];
        $submitted = is_array($state['filters'] ?? null) ? $state['filters'] : [];
        foreach ($definition->filters as $filter) {
            if ($filter->type === 'multiselect') {
                $value = GridFilterValue::many($submitted[$filter->key] ?? []);
                if ($value !== []) {
                    $filters[$filter->key] = $value;
                }
                continue;
            }
            $value = GridFilterValue::one($submitted[$filter->key] ?? '');
            if ($value !== '') {
                $filters[$filter->key] = $value;
            }
        }

        $sortCandidate = GridFilterValue::one($state['sort_by'] ?? null);
        $sortBy = $sortCandidate !== '' && $definition->isSortable($sortCandidate)
            ? $sortCandidate : $definition->defaultSort;
        $sortDir = strtolower(GridFilterValue::one($state['sort_dir'] ?? $definition->defaultSortDir)) === 'asc'
            ? 'asc' : 'desc';
        $pageSize = max(5, min(200, (int) GridFilterValue::one($state['page_size'] ?? 20)));
        $allowed = array_fill_keys($definition->allColumnKeys(), true);

        $submittedColumns = array_key_exists('visible_columns', $state)
            ? GridFilterValue::many($state['visible_columns'])
            : $definition->defaultVisibleColumnKeys();
        $visibleColumns = array_values(array_filter($submittedColumns,
            static fn (string $key): bool => isset($allowed[$key])));
        foreach ($definition->columns as $column) {
            if (!$column->toggleable && !in_array($column->key, $visibleColumns, true)) {
                $visibleColumns[] = $column->key;
            }
        }

        $submittedOrder = array_key_exists('column_order', $state)
            ? GridFilterValue::many($state['column_order'])
            : $definition->allColumnKeys();
        $columnOrder = array_values(array_filter($submittedOrder,
            static fn (string $key): bool => isset($allowed[$key])));
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
