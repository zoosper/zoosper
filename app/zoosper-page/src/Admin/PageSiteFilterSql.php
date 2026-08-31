<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\Grid\GridFilterValue;

/** Builds a bound IN predicate for the Pages Site multiselect. */
final readonly class PageSiteFilterSql
{
    /**
     * @return array{sql: string, parameters: array<string, int>}
     */
    public function build(mixed $siteIds): array
    {
        $ids = [];
        foreach (GridFilterValue::many($siteIds) as $value) {
            if (ctype_digit($value) && (int) $value > 0) {
                $ids[] = (int) $value;
            }
        }
        $ids = array_values(array_unique($ids));
        if ($ids === []) {
            return ['sql' => '', 'parameters' => []];
        }

        $placeholders = [];
        $parameters = [];
        foreach ($ids as $index => $id) {
            $name = 'site_id_' . $index;
            $placeholders[] = ':' . $name;
            $parameters[$name] = $id;
        }

        return [
            'sql' => 'p.site_id IN (' . implode(', ', $placeholders) . ')',
            'parameters' => $parameters,
        ];
    }
}










