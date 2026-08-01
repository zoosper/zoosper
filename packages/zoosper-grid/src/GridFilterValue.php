<?php

declare(strict_types=1);

namespace Zoosper\Grid;

final class GridFilterValue
{
    /** @return list<string> */
    public static function many(mixed $value): array
    {
        $values = is_array($value) ? $value : [$value];
        $normalised = [];
        foreach ($values as $item) {
            $item = trim((string) $item);
            if ($item !== '' && !in_array($item, $normalised, true)) {
                $normalised[] = $item;
            }
        }
        return $normalised;
    }
}
