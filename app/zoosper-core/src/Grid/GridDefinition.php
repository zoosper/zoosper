<?php

declare(strict_types=1);

namespace Zoosper\Core\Grid;

/**
 * Declarative description of an admin grid: its columns, filters, and default
 * sort order. A GridDefinition is pure data — no I/O, no rendering — so it can
 * be unit-tested trivially and shared between the HTML renderer and (later) a
 * JSON/AJAX response mode without any change to this class.
 */
final readonly class GridDefinition
{
    /**
     * @param list<GridColumn> $columns
     * @param list<GridFilter> $filters
     */
    public function __construct(
        public string $title,
        public array $columns,
        public array $filters = [],
        public ?string $defaultSort = null,
        public string $defaultSortDir = 'desc',
        public string $emptyMessage = 'No records found.',
    ) {
    }

    /**
     * @return list<string> keys of columns marked sortable
     */
    public function sortableColumnKeys(): array
    {
        $keys = [];
        foreach ($this->columns as $column) {
            if ($column->sortable) {
                $keys[] = $column->key;
            }
        }

        return $keys;
    }

    public function isSortable(string $key): bool
    {
        return in_array($key, $this->sortableColumnKeys(), true);
    }

    /**
     * @return list<string> keys of every declared filter
     */
    public function filterKeys(): array
    {
        return array_map(static fn (GridFilter $filter): string => $filter->key, $this->filters);
    }
}
