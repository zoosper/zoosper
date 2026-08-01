<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridFilterValue;

/** Immutable, validated Page export criteria derived from resolved Grid state. */
final readonly class PageGridExportCriteria
{
    /** @param list<int> $siteIds */
    public function __construct(
        public string $search,
        public string $status,
        public array $siteIds,
        public string $sortBy,
        public string $sortDir,
    ) {
    }

    public static function fromGridCriteria(GridCriteria $criteria): self
    {
        $siteIds = [];
        foreach (GridFilterValue::many($criteria->filters['site_id'] ?? []) as $value) {
            if (ctype_digit($value) && (int) $value > 0) {
                $siteIds[] = (int) $value;
            }
        }

        return new self(
            search: trim((string) ($criteria->filters['q'] ?? '')),
            status: trim((string) ($criteria->filters['status'] ?? '')),
            siteIds: array_values(array_unique($siteIds)),
            sortBy: (string) ($criteria->sortBy ?? 'id'),
            sortDir: strtolower($criteria->sortDir) === 'asc' ? 'asc' : 'desc',
        );
    }
}
