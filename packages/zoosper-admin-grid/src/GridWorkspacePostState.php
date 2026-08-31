<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Converts POST fields into the canonical state accepted by mutation services. */
final class GridWorkspacePostState
{
    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    public static function fromPost(array $post): array
    {
        return [
            'filters' => is_array($post['filters'] ?? null) ? $post['filters'] : [],
            'sort_by' => (string) ($post['sort_by'] ?? ''),
            'sort_dir' => (string) ($post['sort_dir'] ?? 'desc'),
            'page_size' => (int) ($post['page_size'] ?? 20),
            'visible_columns' => is_array($post['visible_columns'] ?? null)
                ? array_values($post['visible_columns'])
                : [],
            'column_order' => is_array($post['column_order'] ?? null)
                ? array_values($post['column_order'])
                : [],
        ];
    }
}











