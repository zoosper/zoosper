<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/** Converts the Pages GET query into the canonical shared Grid state. */
final class PageGridQueryState
{
    /** @param array<string, mixed> $query @return array<string, mixed> */
    public static function fromQuery(array $query): array
    {
        $state = [
            'filters' => [
                'q' => trim((string) ($query['q'] ?? '')),
                'title' => trim((string) ($query['title'] ?? '')),
                'slug' => trim((string) ($query['slug'] ?? '')),
                'status' => trim((string) ($query['status'] ?? '')),
                'site_id' => is_array($query['site_id'] ?? null)
                    ? array_values($query['site_id'])
                    : (($query['site_id'] ?? '') === '' ? [] : [(string) $query['site_id']]),
            ],
            'sort_by' => trim((string) ($query['sort'] ?? '')),
            'sort_dir' => trim((string) ($query['dir'] ?? '')),
            'page_size' => (int) ($query['page_size'] ?? 20),
        ];

        // Presence is significant: an explicit empty selection means that all
        // toggleable columns were hidden, not that defaults should be restored.
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
}










