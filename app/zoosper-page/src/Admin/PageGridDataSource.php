<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\Core\Grid\GridCriteria;
use Zoosper\Core\Grid\GridDataSourceInterface;
use Zoosper\Core\Pagination\PaginationResult;

final readonly class PageGridDataSource implements GridDataSourceInterface
{
    public function __construct(private PageGridRepository $pages)
    {
    }

    /** @return PaginationResult<array<string, mixed>> */
    public function paginate(GridCriteria $criteria): PaginationResult
    {
        $siteId = isset($criteria->filters['site_id'])
            && (int) $criteria->filters['site_id'] > 0
            ? (int) $criteria->filters['site_id']
            : null;

        return $this->pages->paginate(new PageGridCriteria(
            pager: $criteria->pager,
            query: trim((string) ($criteria->filters['q'] ?? '')),
            status: trim((string) ($criteria->filters['status'] ?? '')),
            siteId: $siteId,
        ));
    }
}
