<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

final readonly class GridCompactToolbarRenderer
{
    public function render(string $viewLabel, bool $dirty, int $pageSize, int $activeFilters, string $exportUrl = '/admin/pages/export'): string
    {
        $label = htmlspecialchars($viewLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $status = $dirty ? 'Unsaved' : 'Saved';
        $count = $activeFilters > 0 ? ' (' . $activeFilters . ')' : '';
        $options = '';
        foreach ([20, 50, 100, 200] as $value) {
            $options .= '<option value="' . $value . '"' . ($value === $pageSize ? ' selected' : '') . '>' . $value . '</option>';
        }

        return '<div class="grid-compact-actions">'
            . '<button type="button" data-grid-toggle="filters" aria-expanded="false">Filters' . $count . '</button>'
            . '<button type="button" data-grid-toggle="columns" aria-expanded="false">Columns</button>'
            . '<a class="button" data-grid-export href="' . htmlspecialchars($exportUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">Export current page</a></div>'
            . '<div class="grid-compact-state"><strong>' . $label . '</strong>'
            . '<span class="grid-compact-status' . ($dirty ? ' is-dirty' : '') . '">' . $status . '</span>'
            . '<label>Per page <select name="page_size" data-grid-page-size>' . $options . '</select></label>'
            . '<button type="button" data-grid-save-view>Save view</button></div>';
    }
}
