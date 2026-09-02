<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

final readonly class GridCompactToolbarRenderer
{
    /**
     * @param list<array{id: int, name: string, state: array<string, mixed>, is_default: bool}> $bookmarks
     * @param list<int> $pageSizeOptions
     */
    public function render(
        string $viewLabel,
        bool $dirty,
        int $pageSize,
        int $activeFilters,
        ?string $exportUrl = null,
        array $bookmarks = [],
        ?int $activeBookmarkId = null,
        string $viewAction = '/',
        array $pageSizeOptions = [20, 50, 100, 200],
    ): string {
        $status = $dirty ? 'Unsaved' : 'Saved';
        $count = $activeFilters > 0 ? ' (' . $activeFilters . ')' : '';
        $pageSizes = new GridWorkspacePageSizeOptions($pageSizeOptions);
        $pageSizeMarkup = '';
        foreach ($pageSizes->values as $value) {
            $pageSizeMarkup .= '<option value="' . $value . '"'
                . ($value === $pageSize ? ' selected' : '') . '>' . $value . '</option>';
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

        return '<div class="grid-compact-toolbar">'
            . '<div class="grid-compact-actions" aria-label="Grid controls">'
            . '<span class="grid-compact-display-tools">'
            . '<button type="button" data-grid-toggle="filters" aria-controls="grid-filters-panel"'
            . ' aria-expanded="false">Filters' . $count . '</button>'
            . '<button type="button" data-grid-toggle="columns" aria-controls="grid-columns-panel"'
            . ' aria-expanded="false">Columns</button>'
            . '</span>'
            . '<span class="grid-compact-view-tools">'
            . '<label class="grid-compact-view-selector"><span class="sr-only">View</span>'
            . '<select id="grid-workspace-view" name="bookmark_view" data-grid-view-selector aria-label="Saved view">'
            . $viewOptions . '</select></label>'
            . '<button type="button" data-grid-settings-toggle aria-expanded="false"'
            . ' aria-controls="grid-workspace-settings" title="Manage saved views"'
            . ' aria-label="Manage saved views">&#8942;</button>'
            . '</span>'
            . ($exportUrl !== null ? '<a class="button" data-grid-export href="' . $this->escape($exportUrl)
                . '">Export current page</a>' : '')
            . '</div>'
            . '<div class="grid-compact-state">'
            . '<span class="grid-compact-status' . ($dirty ? ' is-dirty' : '')
            . '" role="status">' . $status . '</span>'
            . '<label>Per page <select name="page_size" data-grid-page-size aria-label="Rows per page">'
            . $pageSizeMarkup . '</select></label>'
            . '</div></div>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}











