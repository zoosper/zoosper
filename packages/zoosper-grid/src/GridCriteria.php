<?php

declare(strict_types=1);

namespace Zoosper\Grid;

use Zoosper\Core\Pagination\Pager;

final readonly class GridCriteria
{
    /** @param array<string, string|list<string>> $filters */
    public function __construct(
        public Pager $pager,
        public ?string $sortBy,
        public string $sortDir,
        public array $filters = [],
    ) {
    }

    /** @param array<string, mixed> $values */
    public static function fromValues(array $values, GridDefinition $definition): self
    {
        $pager = Pager::fromQuery($values);
        $requestedSort = GridFilterValue::one($values['sort'] ?? '');
        $sortIsValid = $definition->isSortable($requestedSort);
        $sortBy = $sortIsValid ? $requestedSort : $definition->defaultSort;
        $requestedDir = strtolower(GridFilterValue::one($values['dir'] ?? ''));
        $sortDir = $sortIsValid && in_array($requestedDir, ['asc', 'desc'], true)
            ? $requestedDir : $definition->defaultSortDir;

        $filters = [];
        foreach ($definition->filters as $filter) {
            if ($filter->type === 'multiselect') {
                $value = GridFilterValue::many($values[$filter->key] ?? []);
                if ($value !== []) {
                    $filters[$filter->key] = $value;
                }
                continue;
            }
            $value = GridFilterValue::one($values[$filter->key] ?? '');
            if ($value !== '') {
                $filters[$filter->key] = $value;
            }
        }
        return new self($pager, $sortBy, $sortDir, $filters);
    }

    /** @return array<string, string|int|list<string>> */
    public function linkParameters(): array
    {
        $params = ['page_size' => $this->pager->pageSize];
        if ($this->sortBy !== null) {
            $params['sort'] = $this->sortBy;
            $params['dir'] = $this->sortDir;
        }
        foreach ($this->filters as $key => $value) {
            $params[$key] = $value;
        }
        return $params;
    }

    public function toggledSortDir(string $columnKey, string $defaultDir = 'asc'): string
    {
        return $this->sortBy !== $columnKey ? $defaultDir : ($this->sortDir === 'asc' ? 'desc' : 'asc');
    }
}
