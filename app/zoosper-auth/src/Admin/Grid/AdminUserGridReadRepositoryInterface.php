<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

use Zoosper\Pagination\PaginationResult;

interface AdminUserGridReadRepositoryInterface
{
    /** @return PaginationResult<array<string, mixed>> */
    public function paginate(AdminUserGridCriteria $criteria): PaginationResult;
}










