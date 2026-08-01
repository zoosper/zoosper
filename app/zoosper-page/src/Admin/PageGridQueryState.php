<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/** Converts the Pages GET request into the Admin Grid state shape. */
final class PageGridQueryState
{
    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    public static function fromQuery(array $query): array
    {
        $filters = [];
        foreach (['q', 'status', 'site_id'] as $key) {
            if (array_key_exists($key, $query)) {
                $filters[$key] = $query[$key];
            }
        }

        $state = ['filters' => $filters];
        if (isset($query['sort'])) {
            $state['sort_by'] = (string) $query['sort'];
        }
        if (isset($query['dir'])) {
            $state['sort_dir'] = (string) $query['dir'];
        }
        if (isset($query['page_size'])) {
            $state['page_size'] = (int) $query['page_size'];
        }
        if (isset($query['visible_columns']) && is_array($query['visible_columns'])) {
            $state['visible_columns'] = $query['visible_columns'];
        }
        if (isset($query['column_order']) && is_array($query['column_order'])) {
            $state['column_order'] = $query['column_order'];
        }

        return $state;
    }

    /** @param array<string, mixed> $query */
    public static function bookmarkId(array $query): ?int
    {
        $id = isset($query['bookmark_id']) ? (int) $query['bookmark_id'] : 0;
        return $id > 0 ? $id : null;
    }
}
