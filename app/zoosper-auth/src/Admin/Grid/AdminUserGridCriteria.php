<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

use Zoosper\Pagination\Pager;
use Zoosper\Grid\GridCriteria;

/** Normalised read criteria for the Admin Users listing. */
final readonly class AdminUserGridCriteria
{
    public function __construct(
        public Pager $pager,
        public string $query,
        public string $status,
        public ?string $sortBy,
        public string $sortDir,
    ) {
    }

    public static function fromGridCriteria(GridCriteria $criteria): self
    {
        return new self(
            pager: $criteria->pager,
            query: self::scalarFilter($criteria->filters['q'] ?? null),
            status: self::scalarFilter($criteria->filters['status'] ?? null),
            sortBy: $criteria->sortBy,
            sortDir: $criteria->sortDir,
        );
    }

    private static function scalarFilter(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }
}










