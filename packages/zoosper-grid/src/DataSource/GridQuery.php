<?php

declare(strict_types=1);

namespace Zoosper\Grid\DataSource;

use InvalidArgumentException;

/** Immutable, transport-neutral query requested by a Grid page. */
final readonly class GridQuery
{
    /**
     * @param array<string, scalar|list<scalar>|null> $filters
     */
    public function __construct(
        public int $page = 1,
        public int $pageSize = 20,
        public ?string $sort = null,
        public string $direction = 'asc',
        public array $filters = [],
        public ?string $search = null,
        public ?string $cursor = null,
    ) {
        if ($page < 1) {
            throw new InvalidArgumentException('Grid page must be at least 1.');
        }
        if ($pageSize < 1) {
            throw new InvalidArgumentException('Grid page size must be at least 1.');
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException('Grid direction must be asc or desc.');
        }
        if ($sort !== null && trim($sort) === '') {
            throw new InvalidArgumentException('Grid sort key cannot be empty.');
        }
    }
}











