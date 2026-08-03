<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

final readonly class GridCompactToolbarRenderer
{
    /**
     * @param list<array{id: int, name: string, state: array<string, mixed>, is_default: bool}> $bookmarks
     */
    public function render(
        string $viewLabel,
        bool $dirty,
        int $pageSize,
        int $activeFilters,
        string $exportUrl = '/admin/pages/export',
        array $bookmarks = [],
        ?int $activeBookmarkId = null,
        string $viewAction = '/admin/pages',
    ): string {
        $status = $dirty ? 'Unsaved' : 'Saved';
        $count = $activeFilters > 0 ? ' (' . $activeFilters . ')' : '';
        $options = '';
        foreach ([20, 50, 100, 200] as $value) {
            $options .= '<option value="' . $value . '"' . ($value === $pageSize ? ' selected' : '') . '>' . $value . '</option>';
        }

        $viewOptions = '<option value="' . $this->escape($viewAction) . '"'
            . ($activeBookmarkId === null ? ' selected' : '') . '>Default view</option>';
        foreach ($bookmarks as $bookmark) {
            $url = $viewAction . '?' . http_build_query(
                ['bookmark_id' => (int) $bookmark['id']],
                '',
                '&',
                PHP_QUERY_RFC3986,
            );
            $name = (string) $bookmark['name'];
            if ((bool) $bookmark['is_default']) {
                $name .= ' (default)';
            }
            $viewOptions .= '<option value="' . $this->escape($url) . '"'
                . ((int) $bookmark['id'] === $activeBookmarkId ? ' selected' : '') . '>'
                . $this->escape($name) . '</option>';
        }

        return '<div class="grid-compact-actions">'
            . '<button type="button" data-grid-toggle="filters" aria-expanded="false">Filters' . $count . '</button>'
            . '<button type="button" data-grid-toggle="columns" aria-expanded="false">Columns</button>'
            . '<a class="button" data-grid-export href="' . $this->escape($exportUrl) . '">Export current page</a></div>'
            . '<div class="grid-compact-state">'
            . '<label class="grid-compact-view-selector">View <select data-grid-view-selector aria-label="Saved view">'
            . $viewOptions . '</select></label>'
            . '<span class="grid-compact-status' . ($dirty ? ' is-dirty' : '') . '">' . $status . '</span>'
            . '<label>Per page <select name="page_size" data-grid-page-size>' . $options . '</select></label>'
            . '</div>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
