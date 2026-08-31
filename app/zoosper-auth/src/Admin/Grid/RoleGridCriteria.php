<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

use Zoosper\Pagination\Pager;
use Zoosper\Grid\GridCriteria;

/** Normalised read criteria for the Roles listing. */
final readonly class RoleGridCriteria
{
    public function __construct(
        public Pager $pager,
        public string $query,
        public ?string $sortBy,
        public string $sortDir,
    ) {
    }

    public static function fromGridCriteria(GridCriteria $criteria): self
    {
        return new self(
            pager: $criteria->pager,
            query: trim((string) ($criteria->filters['q'] ?? '')),
            sortBy: $criteria->sortBy,
            sortDir: $criteria->sortDir,
        );
    }
}










