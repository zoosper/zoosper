<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/** Feature-owned streaming repository contract for Page Grid exports. */
interface PageGridExportRepositoryInterface
{
    /** @return iterable<array<string, mixed>> */
    public function stream(PageGridExportCriteria $criteria): iterable;
}
