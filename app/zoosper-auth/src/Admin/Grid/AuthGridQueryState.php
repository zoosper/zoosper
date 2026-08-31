<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

/** Array-safe request state accepted by the Auth Grid listings. */
final class AuthGridQueryState
{
    /** @var list<string> */
    private const KEYS = [
        'page', 'page_size', 'sort', 'dir', 'q', 'status',
        'visible_columns', 'column_order', 'bookmark_id',
    ];

    /** @param array<string, mixed> $query */
    public static function fromQuery(array $query): array
    {
        $state = [];
        foreach (self::KEYS as $key) {
            if (!array_key_exists($key, $query)) {
                continue;
            }
            $value = $query[$key];
            if (is_scalar($value)) {
                $state[$key] = trim((string) $value);
                continue;
            }
            if (is_array($value)) {
                $state[$key] = self::stringList($value);
            }
        }

        return $state;
    }

    /** @param array<string, mixed> $query */
    public static function bookmarkId(array $query): ?int
    {
        $value = $query['bookmark_id'] ?? null;
        return is_scalar($value) && ctype_digit((string) $value) && (int) $value > 0
            ? (int) $value
            : null;
    }

    /** @param array<mixed> $values @return list<string> */
    private static function stringList(array $values): array
    {
        $result = [];
        foreach ($values as $value) {
            if (!is_scalar($value)) {
                continue;
            }
            $item = trim((string) $value);
            if ($item !== '' && !in_array($item, $result, true)) {
                $result[] = $item;
            }
        }
        return $result;
    }
}










