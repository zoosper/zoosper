<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Builds allow-listed Page export SQL fragments without interpolating values.
 * The repository owns the SELECT and applies this plan with PDO binding.
 */
final readonly class PageGridExportSqlBuilder
{
    /** @var array<string, string> */
    private const SORT_COLUMNS = [
        'id' => 'p.id',
        'title' => 'p.title',
        'slug' => 'p.slug',
        'status' => 'p.status',
        'site_id' => 'p.site_id',
        'created_at' => 'p.created_at',
        'updated_at' => 'p.updated_at',
    ];

    public function build(PageGridExportCriteria $criteria): PageGridExportSqlPlan
    {
        $where = [];
        $parameters = [];

        if ($criteria->search !== '') {
            $where[] = '(p.title LIKE :export_search OR p.slug LIKE :export_search)';
            $parameters['export_search'] = '%' . $criteria->search . '%';
        }
        if ($criteria->status !== '') {
            $where[] = 'p.status = :export_status';
            $parameters['export_status'] = $criteria->status;
        }
        if ($criteria->siteIds !== []) {
            $placeholders = [];
            foreach ($criteria->siteIds as $index => $siteId) {
                $name = 'export_site_id_' . $index;
                $placeholders[] = ':' . $name;
                $parameters[$name] = $siteId;
            }
            $where[] = 'p.site_id IN (' . implode(', ', $placeholders) . ')';
        }

        $sortColumn = self::SORT_COLUMNS[$criteria->sortBy] ?? self::SORT_COLUMNS['id'];
        $sortDirection = $criteria->sortDir === 'asc' ? 'ASC' : 'DESC';

        return new PageGridExportSqlPlan(
            whereSql: $where === [] ? '' : 'WHERE ' . implode(' AND ', $where),
            orderSql: 'ORDER BY ' . $sortColumn . ' ' . $sortDirection,
            parameters: $parameters,
        );
    }
}










