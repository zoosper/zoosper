<?php

declare(strict_types=1);

namespace Zoosper\Grid\DataSource;

use InvalidArgumentException;

/**
 * Transport-neutral collection result.
 *
 * @template TItem
 */
final readonly class GridResult
{
    /**
     * @param list<TItem> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $pageSize,
        public GridPaginationMode $paginationMode = GridPaginationMode::Numbered,
        public ?string $nextCursor = null,
        public ?string $previousCursor = null,
    ) {
        if ($total < 0) {
            throw new InvalidArgumentException('Grid total cannot be negative.');
        }
        if ($page < 1 || $pageSize < 1) {
            throw new InvalidArgumentException('Grid result page and page size must be positive.');
        }
        if ($paginationMode === GridPaginationMode::Numbered
            && ($nextCursor !== null || $previousCursor !== null)) {
            throw new InvalidArgumentException('Numbered Grid results cannot expose cursors.');
        }
    }

    public function totalPages(): int
    {
        if ($this->total === 0) {
            return 0;
        }

        return (int) ceil($this->total / $this->pageSize);
    }
}
