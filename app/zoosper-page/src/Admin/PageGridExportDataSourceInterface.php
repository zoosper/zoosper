<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\Grid\GridCriteria;

/** Supplies Pages export rows using the resolved screen criteria. */
interface PageGridExportDataSourceInterface
{
    /** @return iterable<array<string, mixed>> */
    public function exportRows(GridCriteria $criteria): iterable;
}










