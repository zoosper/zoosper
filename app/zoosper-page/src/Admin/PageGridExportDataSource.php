<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\Grid\GridCriteria;

/** Adapts resolved Grid criteria to the Page-owned export repository. */
final readonly class PageGridExportDataSource implements PageGridExportDataSourceInterface
{
    public function __construct(private PageGridExportRepositoryInterface $repository)
    {
    }

    public function exportRows(GridCriteria $criteria): iterable
    {
        return $this->repository->stream(
            PageGridExportCriteria::fromGridCriteria($criteria),
        );
    }
}










