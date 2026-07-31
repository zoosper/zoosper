<?php

declare(strict_types=1);

namespace Zoosper\Grid;

use Zoosper\Core\Pagination\Pager;

/**
 * Generic pagination + sort + filter criteria for the shared admin Grid
 * engine, replacing the bespoke *Criteria class every screen used to need
 * (PageGridCriteria, AuditLogCriteria, LoginHistoryCriteria...). ONE criteria
 * shape now serves every grid; only the GridDefinition differs per screen.
 *
 * Reuses the existing Zoosper\Core\Pagination\Pager unchanged.
 */
final readonly class GridCriteria
{
    /**
     * @param array<string, string> $filters filter key => submitted value
     */
    public function __construct(
        public Pager $pager,
        public ?string $sortBy,
        public string $sortDir,
        public array $filters = [],
    ) {
    }

    /**
     * Build criteria from raw request values (already extracted from
     * Request::query() by the controller — Request::query() is single-key
     * only, so callers must supply the specific keys their GridDefinition
     * declares; see AdminGridController::queryValues()).
     *
     * @param array<string, mixed> $values
     */
    public static function fromValues(array $values, GridDefinition $definition): self
    {
        $pager = Pager::fromQuery($values);

        $requestedSort = trim((string) ($values['sort'] ?? ''));
        $sortIsValid = $definition->isSortable($requestedSort);
        $sortBy = $sortIsValid ? $requestedSort : $definition->defaultSort;

        // A requested direction is only trusted when paired with a VALID sort
        // column, so an invalid sort request always falls back to a coherent
        // (column, direction) pair from the definition, never a mix of the
        // default column with an arbitrary requested direction.
        $requestedDir = strtolower(trim((string) ($values['dir'] ?? '')));
        $dirIsValid = in_array($requestedDir, ['asc', 'desc'], true);
        $sortDir = ($sortIsValid && $dirIsValid) ? $requestedDir : $definition->defaultSortDir;

        $filters = [];
        foreach ($definition->filterKeys() as $key) {
            $value = trim((string) ($values[$key] ?? ''));
            if ($value !== '') {
                $filters[$key] = $value;
            }
        }

        return new self($pager, $sortBy, $sortDir, $filters);
    }

    /**
     * Query-string parameters that must be preserved across pagination/sort
     * links so filters and page size are not lost when a link is followed.
     *
     * @return array<string, string|int>
     */
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

    /**
     * Toggle sort direction for a given column: if it is already the active
     * sort, flip asc/desc; otherwise start with the given default direction.
     */
    public function toggledSortDir(string $columnKey, string $defaultDir = 'asc'): string
    {
        if ($this->sortBy !== $columnKey) {
            return $defaultDir;
        }

        return $this->sortDir === 'asc' ? 'desc' : 'asc';
    }
}

