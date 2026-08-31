<?php

declare(strict_types=1);

namespace Zoosper\Pagination;

use Marko\Pagination\OffsetPaginator;

/**
 * Immutable, Zoosper-owned pagination result for grids and feature modules.
 *
 * Marko provides the offset calculations behind this boundary, but is never
 * exposed in public Zoosper signatures.
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
        return $this->paginator()->lastPage();
    }

    public function hasPrevious(): bool
    {
        return $this->paginator()->previousPage() !== null;
    }

    public function hasNext(): bool
    {
        // Preserve the historical public-constructor behaviour for an invalid
        // directly-constructed page while Pager continues to normalize input.
        if ($this->page < 1) {
            return $this->page < $this->totalPages();
        }

        return $this->paginator()->hasMorePages();
    }

    private function paginator(): OffsetPaginator
    {
        return new OffsetPaginator(
            items: $this->items,
            total: $this->total,
            perPage: max(1, $this->pageSize),
            currentPage: max(1, $this->page),
        );
    }
}











