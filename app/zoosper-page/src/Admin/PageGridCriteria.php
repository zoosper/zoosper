<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\Core\Pagination\Pager;

final readonly class PageGridCriteria
{
    /** @param list<int> $siteIds */
    public function __construct(
        public Pager $pager,
        public string $query = '',
        public string $status = '',
        public ?int $siteId = null,
        public ?string $sortBy = null,
        public string $sortDir = 'desc',
        public array $siteIds = [],
        public string $title = '',
        public string $slug = '',
    ) {
    }
}
