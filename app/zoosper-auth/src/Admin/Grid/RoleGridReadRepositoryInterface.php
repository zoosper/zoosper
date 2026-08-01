<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

use Zoosper\Core\Pagination\PaginationResult;

interface RoleGridReadRepositoryInterface
{
    /** @return PaginationResult<array<string, mixed>> */
    public function paginate(RoleGridCriteria $criteria): PaginationResult;
}
