<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

/** Builds safe query fragments for the Admin Users read model. */
final readonly class AdminUserGridSqlBuilder
{
    /** @var array<string, string> */
    private const SORT_COLUMNS = [
        'id' => 'u.id',
        'name' => 'u.name',
        'email' => 'u.email',
        'status' => 'u.status',
    ];

    public function build(AdminUserGridCriteria $criteria): AuthGridSqlPlan
    {
        $conditions = [];
        $parameters = [];

        if ($criteria->query !== '') {
            $conditions[] = '(u.name LIKE :grid_query OR u.email LIKE :grid_query)';
            $parameters['grid_query'] = '%' . $criteria->query . '%';
        }

        if ($criteria->status !== '') {
            $conditions[] = 'u.status = :grid_status';
            $parameters['grid_status'] = $criteria->status;
        }

        $column = self::SORT_COLUMNS[$criteria->sortBy ?? ''] ?? 'u.id';
        $direction = $criteria->sortDir === 'asc' ? 'ASC' : 'DESC';

        return new AuthGridSqlPlan(
            whereSql: $conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions),
            orderSql: $column . ' ' . $direction . ', u.id DESC',
            parameters: $parameters,
        );
    }
}










