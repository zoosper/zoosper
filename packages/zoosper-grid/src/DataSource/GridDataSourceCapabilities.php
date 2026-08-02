<?php

declare(strict_types=1);

namespace Zoosper\Grid\DataSource;

/** Declares only controls the backing collection can honour globally. */
final readonly class GridDataSourceCapabilities
{
    /**
     * @param list<string> $sortableColumns
     * @param list<string> $filterableFields
     */
    public function __construct(
        public GridPaginationMode $paginationMode = GridPaginationMode::Numbered,
        public bool $searchable = false,
        public bool $exportable = false,
        public array $sortableColumns = [],
        public array $filterableFields = [],
    ) {
    }

    public function supportsSort(string $column): bool
    {
        return in_array($column, $this->sortableColumns, true);
    }

    public function supportsFilter(string $field): bool
    {
        return in_array($field, $this->filterableFields, true);
    }
}
