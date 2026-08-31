<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Admin;

/** Translates flat HTTP query parameters into the shared Grid workspace state shape. */
final class StoreOrderGridQueryState
{
    private const FILTER_KEYS = [
        'store_code',
        'kiosk_website_id',
        'order_id',
        'customer',
        'status',
        'placed_from',
        'placed_to',
    ];

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    public static function fromQuery(array $query): array
    {
        $filters = [];
        foreach (self::FILTER_KEYS as $key) {
            if (array_key_exists($key, $query)) {
                $filters[$key] = $query[$key];
            }
        }

        $state = [
            'filters' => $filters,
            'page' => max(1, (int) ($query['page'] ?? 1)),
            'page_size' => (int) ($query['page_size'] ?? 20),
            'sort_by' => isset($query['sort']) ? (string) $query['sort'] : null,
            'sort_dir' => (string) ($query['dir'] ?? 'desc'),
        ];

        if (array_key_exists('visible_columns', $query)) {
            $state['visible_columns'] = is_array($query['visible_columns'])
                ? array_values($query['visible_columns'])
                : [];
        }
        if (array_key_exists('column_order', $query)) {
            $state['column_order'] = is_array($query['column_order'])
                ? array_values($query['column_order'])
                : [];
        }

        return $state;
    }

    /** @param array<string, mixed> $query */
    public static function bookmarkId(array $query): ?int
    {
        $id = (int) ($query['bookmark_id'] ?? 0);
        return $id > 0 ? $id : null;
    }

    private function __construct()
    {
    }
}











