<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

use Zoosper\Core\Pagination\PaginationResult;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDataSourceInterface;

final readonly class RoleGridDataSource implements GridDataSourceInterface
{
    public function __construct(private RoleGridReadRepositoryInterface $roles)
    {
    }

    /** @return PaginationResult<array<string, mixed>> */
    public function paginate(GridCriteria $criteria): PaginationResult
    {
        return $this->roles->paginate(RoleGridCriteria::fromGridCriteria($criteria));
    }
}
