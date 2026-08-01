<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/** SQL fragment and separately bound parameters for a Page export query. */
final readonly class PageGridExportSqlPlan
{
    /** @param array<string, int|string> $parameters */
    public function __construct(
        public string $whereSql,
        public string $orderSql,
        public array $parameters,
    ) {
    }
}
