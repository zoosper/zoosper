<?php

declare(strict_types=1);

namespace Zoosper\Core\Grid;

use Zoosper\Core\Pagination\PaginationResult;

/**
 * Renders a complete admin grid (filter bar + sortable table + pagination
 * controls) as an HTML string, from a GridDefinition + PaginationResult +
 * GridCriteria. This is the ONE shared renderer every admin listing uses, so
 * every grid gets the same filtering/sorting/pagination UX and CSS hooks for
 * free, and a future AJAX mode only needs to change what wraps this output,
 * not rewrite each grid's markup.
 *
 * Pure and dependency-free: no template engine required, so it works
 * regardless of which theme's template engine (PHP or Latte) a module uses —
 * a controller calls render() once and hands the resulting HTML string to
 * whichever renderer/template it already uses (e.g. as a `gridHtml` view-data
 * key echoed directly in a template).
 */
final class GridHtmlRenderer
{
    /**
     * @param PaginationResult<array<string, mixed>> $result
     */
    public function render(GridDefinition $definition, PaginationResult $result, GridCriteria $criteria, string $baseUrl): string
    {
        return '<div class="grid">'
            . $this->renderFilterBar($definition, $criteria, $baseUrl)
            . $this->renderSummary($result)
            . $this->renderTable($definition, $result, $criteria, $baseUrl)
            . $this->renderPagination($result, $criteria, $baseUrl)
            . '</div>';
    }

    private function renderFilterBar(GridDefinition $definition, GridCriteria $criteria, string $baseUrl): string
    {
        if ($definition->filters === []) {
            return '';
        }

        $fields = '';
        foreach ($definition->filters as $filter) {
            $current = $criteria->filters[$filter->key] ?? '';
            $fields .= $this->renderFilterField($filter, $current);
        }

        // Preserve sort state across a filter submission; page resets to 1.
        $hidden = '';
        if ($criteria->sortBy !== null) {
            $hidden .= '<input type="hidden" name="sort" value="' . $this->e($criteria->sortBy) . '">';
            $hidden .= '<input type="hidden" name="dir" value="' . $this->e($criteria->sortDir) . '">';
        }
        $hidden .= '<input type="hidden" name="page_size" value="' . (int) $criteria->pager->pageSize . '">';

        return '<form method="get" action="' . $this->e($baseUrl) . '" class="grid-filters">'
            . $fields
            . $hidden
            . '<button type="submit" class="grid-filters__apply">Filter</button>'
            . '<a href="' . $this->e($baseUrl) . '" class="grid-filters__reset">Reset</a>'
            . '</form>';
    }

    private function renderFilterField(GridFilter $filter, string $current): string
    {
        $name = $this->e($filter->key);
        $label = $this->e($filter->label);

        if ($filter->type === 'select') {
            $options = '<option value="">' . $label . '</option>';
            foreach ($filter->options as $option) {
                $selected = $option['value'] === $current ? ' selected' : '';
                $options .= '<option value="' . $this->e($option['value']) . '"' . $selected . '>'
                    . $this->e($option['label']) . '</option>';
            }

            return '<label class="grid-filters__field"><span>' . $label . '</span>'
                . '<select name="' . $name . '">' . $options . '</select></label>';
        }

        return '<label class="grid-filters__field"><span>' . $label . '</span>'
            . '<input type="text" name="' . $name . '" value="' . $this->e($current) . '" placeholder="' . $label . '"></label>';
    }

    /**
     * @param PaginationResult<array<string, mixed>> $result
     */
    private function renderSummary(PaginationResult $result): string
    {
        if ($result->total === 0) {
            return '';
        }

        $from = ($result->page - 1) * $result->pageSize + 1;
        $to = min($result->total, $result->page * $result->pageSize);

        return '<p class="grid-summary">Showing ' . $from . '&ndash;' . $to . ' of ' . $result->total . '</p>';
    }

    /**
     * @param PaginationResult<array<string, mixed>> $result
     */
    private function renderTable(GridDefinition $definition, PaginationResult $result, GridCriteria $criteria, string $baseUrl): string
    {
        $head = '';
        foreach ($definition->columns as $column) {
            $head .= $this->renderHeaderCell($column, $criteria, $baseUrl);
        }

        $body = '';
        if ($result->items === []) {
            $colspan = count($definition->columns);
            $body = '<tr><td colspan="' . $colspan . '" class="grid-empty">'
                . $this->e($definition->emptyMessage) . '</td></tr>';
        } else {
            foreach ($result->items as $row) {
                $body .= '<tr>';
                foreach ($definition->columns as $column) {
                    $value = $row[$column->key] ?? null;
                    $align = $column->align !== 'left' ? ' style="text-align:' . $this->e($column->align) . '"' : '';
                    $body .= '<td' . $align . '>' . $column->renderValue($value, $row) . '</td>';
                }
                $body .= '</tr>';
            }
        }

        return '<table class="grid-table"><thead><tr>' . $head . '</tr></thead><tbody>' . $body . '</tbody></table>';
    }

    private function renderHeaderCell(GridColumn $column, GridCriteria $criteria, string $baseUrl): string
    {
        $label = $this->e($column->label);

        if (!$column->sortable) {
            return '<th>' . $label . '</th>';
        }

        $isActive = $criteria->sortBy === $column->key;
        $nextDir = $criteria->toggledSortDir($column->key);
        $params = array_merge($criteria->linkParameters(), ['sort' => $column->key, 'dir' => $nextDir]);
        unset($params['page']); // sorting resets to page 1

        $indicator = $isActive ? ($criteria->sortDir === 'asc' ? ' &#9650;' : ' &#9660;') : '';
        $activeClass = $isActive ? ' grid-sort--active' : '';

        return '<th class="grid-sortable' . $activeClass . '">'
            . '<a href="' . $this->e($this->url($baseUrl, $params)) . '">' . $label . $indicator . '</a></th>';
    }

    /**
     * @param PaginationResult<array<string, mixed>> $result
     */
    private function renderPagination(PaginationResult $result, GridCriteria $criteria, string $baseUrl): string
    {
        if ($result->total === 0) {
            return '';
        }

        $totalPages = $result->totalPages();
        $base = $criteria->linkParameters();

        $prev = $result->hasPrevious()
            ? '<a href="' . $this->e($this->url($baseUrl, array_merge($base, ['page' => $result->page - 1]))) . '" class="grid-pagination__prev">&laquo; Previous</a>'
            : '<span class="grid-pagination__prev grid-pagination__disabled">&laquo; Previous</span>';

        $next = $result->hasNext()
            ? '<a href="' . $this->e($this->url($baseUrl, array_merge($base, ['page' => $result->page + 1]))) . '" class="grid-pagination__next">Next &raquo;</a>'
            : '<span class="grid-pagination__next grid-pagination__disabled">Next &raquo;</span>';

        return '<nav class="grid-pagination">'
            . $prev
            . '<span class="grid-pagination__status">Page ' . $result->page . ' of ' . $totalPages . '</span>'
            . $next
            . '</nav>';
    }

    /**
     * @param array<string, string|int> $params
     */
    private function url(string $baseUrl, array $params): string
    {
        $filtered = array_filter($params, static fn (mixed $v): bool => $v !== null && $v !== '');
        $query = http_build_query($filtered);

        return $query !== '' ? $baseUrl . '?' . $query : $baseUrl;
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
