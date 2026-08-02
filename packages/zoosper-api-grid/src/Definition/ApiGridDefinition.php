<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Definition;

use InvalidArgumentException;
use Zoosper\Grid\GridDefinition;

/** Immutable registration for one reusable API-backed Grid page. */
final readonly class ApiGridDefinition
{
    /** @param list<int> $pageSizes */
    public function __construct(
        public string $key,
        public string $title,
        public string $route,
        public string $permission,
        public string $dataSourceService,
        public GridDefinition $grid,
        public array $pageSizes = [20, 50, 100],
        public ?string $exportPermission = null,
    ) {
        if (preg_match('/^[a-z][a-z0-9_.-]*$/', $key) !== 1) {
            throw new InvalidArgumentException('API Grid key must be a stable lowercase identifier.');
        }
        if ($title === '' || $permission === '' || $dataSourceService === '') {
            throw new InvalidArgumentException('API Grid title, permission and data-source service are required.');
        }
        if (!str_starts_with($route, '/admin/')) {
            throw new InvalidArgumentException('API Grid route must live under /admin/.');
        }
        if ($pageSizes === [] || array_filter($pageSizes, static fn (int $size): bool => $size < 1) !== []) {
            throw new InvalidArgumentException('API Grid page sizes must be positive.');
        }
    }
}
