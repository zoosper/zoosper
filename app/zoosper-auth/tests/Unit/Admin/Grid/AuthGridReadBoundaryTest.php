<?php

declare(strict_types=1);

use Zoosper\Auth\Admin\Grid\AdminUserGridCriteria;
use Zoosper\Auth\Admin\Grid\AdminUserGridDataSource;
use Zoosper\Auth\Admin\Grid\AdminUserGridReadRepositoryInterface;
use Zoosper\Auth\Admin\Grid\RoleGridCriteria;
use Zoosper\Auth\Admin\Grid\RoleGridDataSource;
use Zoosper\Auth\Admin\Grid\RoleGridReadRepositoryInterface;
use Zoosper\Pagination\Pager;
use Zoosper\Pagination\PaginationResult;
use Zoosper\Grid\GridCriteria;

it('maps generic Grid criteria into the Admin Users read boundary', function (): void {
    $repository = new class implements AdminUserGridReadRepositoryInterface {
        public ?AdminUserGridCriteria $received = null;

        public function paginate(AdminUserGridCriteria $criteria): PaginationResult
        {
            $this->received = $criteria;

            return new PaginationResult([], 0, $criteria->pager->page, $criteria->pager->pageSize);
        }
    };

    $criteria = new GridCriteria(
        new Pager(page: 3, pageSize: 50),
        'email',
        'asc',
        ['q' => '  admin@example.test  ', 'status' => ' active '],
    );

    (new AdminUserGridDataSource($repository))->paginate($criteria);

    expect($repository->received)->not->toBeNull()
        ->and($repository->received?->query)->toBe('admin@example.test')
        ->and($repository->received?->status)->toBe('active')
        ->and($repository->received?->sortBy)->toBe('email')
        ->and($repository->received?->sortDir)->toBe('asc')
        ->and($repository->received?->pager->page)->toBe(3)
        ->and($repository->received?->pager->pageSize)->toBe(50);
});

it('maps generic Grid criteria into the Roles read boundary', function (): void {
    $repository = new class implements RoleGridReadRepositoryInterface {
        public ?RoleGridCriteria $received = null;

        public function paginate(RoleGridCriteria $criteria): PaginationResult
        {
            $this->received = $criteria;

            return new PaginationResult([], 0, $criteria->pager->page, $criteria->pager->pageSize);
        }
    };

    $criteria = new GridCriteria(
        new Pager(page: 2, pageSize: 100),
        'label',
        'desc',
        ['q' => '  content  '],
    );

    (new RoleGridDataSource($repository))->paginate($criteria);

    expect($repository->received)->not->toBeNull()
        ->and($repository->received?->query)->toBe('content')
        ->and($repository->received?->sortBy)->toBe('label')
        ->and($repository->received?->sortDir)->toBe('desc')
        ->and($repository->received?->pager->page)->toBe(2)
        ->and($repository->received?->pager->pageSize)->toBe(100);
});
