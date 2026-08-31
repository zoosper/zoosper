<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

use Zoosper\Pagination\PaginationResult;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDataSourceInterface;

final readonly class AdminUserGridDataSource implements GridDataSourceInterface
{
    public function __construct(private AdminUserGridReadRepositoryInterface $users)
    {
    }

    /** @return PaginationResult<array<string, mixed>> */
    public function paginate(GridCriteria $criteria): PaginationResult
    {
        return $this->users->paginate(AdminUserGridCriteria::fromGridCriteria($criteria));
    }
}










