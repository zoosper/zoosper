<?php

declare(strict_types=1);

namespace Zoosper\Grid;

use Zoosper\Pagination\PaginationResult;

/**
 * Contract every module's grid-backing repository implements. This is the
 * ONLY method a new admin listing needs to add to get pagination, filtering
 * and sorting: everything else (URL parsing, HTML rendering) is shared.
 */
interface GridDataSourceInterface
{
    /**
     * @return PaginationResult<array<string, mixed>>
     */
    public function paginate(GridCriteria $criteria): PaginationResult;
}












