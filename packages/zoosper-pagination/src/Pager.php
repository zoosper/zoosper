<?php

declare(strict_types=1);

namespace Zoosper\Pagination;

/**
 * Normalises pagination request values for admin grids.
 *
 * Clamps page size and maximum page to safe bounds to prevent memory degradation
 * or excessive database offset overhead.
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
    public static function fromQuery(array $query, int $defaultPageSize = 20, int $maxPageSize = 100, int $maxPage = 100_000): self
    {
        $requestedPage = max(1, (int) ($query['page'] ?? 1));
        $page = min($maxPage, $requestedPage);

        $requestedSize = (int) ($query['page_size'] ?? $defaultPageSize);
        $pageSize = max(1, min($maxPageSize, $requestedSize > 0 ? $requestedSize : $defaultPageSize));

        return new self($page, $pageSize);
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->pageSize;
    }
}











