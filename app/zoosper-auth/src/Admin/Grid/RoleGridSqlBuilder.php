<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

/** Builds safe query fragments for the Roles read model. */
final readonly class RoleGridSqlBuilder
{
    /** @var array<string, string> */
    private const SORT_COLUMNS = [
        'id' => 'r.id',
        'label' => 'r.label',
        'code' => 'r.code',
    ];

    public function build(RoleGridCriteria $criteria): AuthGridSqlPlan
    {
        $conditions = [];
        $parameters = [];

        if ($criteria->query !== '') {
            $query = '%' . $criteria->query . '%';
            $conditions[] = '(r.label LIKE :grid_query_label OR r.code LIKE :grid_query_code)';
            $parameters['grid_query_label'] = $query;
            $parameters['grid_query_code'] = $query;
        }

        $column = self::SORT_COLUMNS[$criteria->sortBy ?? ''] ?? 'r.id';
        $direction = $criteria->sortDir === 'asc' ? 'ASC' : 'DESC';

        return new AuthGridSqlPlan(
            whereSql: $conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions),
            orderSql: $column . ' ' . $direction . ', r.id DESC',
            parameters: $parameters,
        );
    }
}










