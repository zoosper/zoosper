<?php

declare(strict_types=1);

namespace Zoosper\Grid;

final class GridFilterValue
{
    /** @return list<string> */
    public static function many(mixed $value): array
    {
        $normalised = [];
        $append = static function (mixed $item) use (&$normalised): void {
            if (!is_scalar($item)) {
                return;
            }
            $item = trim((string) $item);
            if ($item !== '' && !in_array($item, $normalised, true)) {
                $normalised[] = $item;
            }
        };
        is_array($value) ? array_walk_recursive($value, $append) : $append($value);
        return $normalised;
    }

    public static function one(mixed $value): string
    {
        if (is_array($value) || !is_scalar($value)) {
            return '';
        }
        return trim((string) $value);
    }
}
