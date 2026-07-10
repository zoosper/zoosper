<?php

declare(strict_types=1);

namespace Zoosper\Core\Pagination;

/**
 * Normalises pagination request values for admin grids.
 *
 * The class deliberately clamps page size to a safe maximum so large admin
 * requests cannot accidentally pull an unbounded dataset into memory.
 */
final readonly class Pager
{
    public function __construct(
        public int $page,
        public int $pageSize,
    ) {
    }

    /**
     * Build a pager from raw query values.
     *
     * @param array<string, mixed> $query Request query parameters.
     */
    public static function fromQuery(array $query, int $defaultPageSize = 20, int $maxPageSize = 100): self
    {
        $page = max(1, (int) ($query['page'] ?? 1));
        $requestedSize = (int) ($query['page_size'] ?? $defaultPageSize);
        $pageSize = max(1, min($maxPageSize, $requestedSize > 0 ? $requestedSize : $defaultPageSize));

        return new self($page, $pageSize);
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->pageSize;
    }
}
