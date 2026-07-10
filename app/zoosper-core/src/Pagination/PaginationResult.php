<?php

declare(strict_types=1);

namespace Zoosper\Core\Pagination;

/**
 * Immutable pagination result for admin grids.
 *
 * @template T
 */
final readonly class PaginationResult
{
    /**
     * @param list<T> $items Current page records.
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $pageSize,
    ) {
    }

    public function totalPages(): int
    {
        return max(1, (int) ceil($this->total / max(1, $this->pageSize)));
    }

    public function hasPrevious(): bool
    {
        return $this->page > 1;
    }

    public function hasNext(): bool
    {
        return $this->page < $this->totalPages();
    }
}
