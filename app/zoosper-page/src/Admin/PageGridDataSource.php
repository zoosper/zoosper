<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\Pagination\PaginationResult;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDataSourceInterface;
use Zoosper\Grid\GridFilterValue;

final readonly class PageGridDataSource implements GridDataSourceInterface
{
    public function __construct(private PageGridRepository $pages)
    {
    }

    /** @return PaginationResult<array<string, mixed>> */
    #[\Override]
    public function paginate(GridCriteria $criteria): PaginationResult
    {
        $siteIds = [];
        foreach (GridFilterValue::many($criteria->filters['site_id'] ?? []) as $value) {
            if (ctype_digit($value) && (int) $value > 0) {
                $siteIds[] = (int) $value;
            }
        }

        return $this->pages->paginate(new PageGridCriteria(
            pager: $criteria->pager,
            query: self::scalarFilter($criteria->filters['q'] ?? null),
            status: self::scalarFilter($criteria->filters['status'] ?? null),
            siteId: $siteIds[0] ?? null,
            sortBy: $criteria->sortBy,
            sortDir: $criteria->sortDir,
            siteIds: array_values(array_unique($siteIds)),
            title: self::scalarFilter($criteria->filters['title'] ?? null),
            slug: self::scalarFilter($criteria->filters['slug'] ?? null),
        ));
    }

    private static function scalarFilter(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }
}










