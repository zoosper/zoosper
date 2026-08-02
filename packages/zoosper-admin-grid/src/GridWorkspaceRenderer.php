<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridDefinition;
use Zoosper\Grid\GridFilter;
use Zoosper\Grid\GridFilterOption;

/**
 * Renders the reusable, accessible Grid workspace controls.
 *
 * This renderer intentionally emits no inline JavaScript. Behaviour is attached
 * by the admin asset layer using data attributes, keeping CSP compatibility and
 * allowing pointer, keyboard and no-JavaScript interaction paths.
 */
final readonly class GridWorkspaceRenderer
{
    public function render(GridViewState $state, string $formAction): string
    {
        $definition = $state->definition;
        $html = '<section class="grid-workspace" data-grid-workspace>';
        $html .= '<div class="grid-workspace__bar">';
        $html .= $this->renderViewSelector($state);
        $html .= '<button type="button" data-grid-panel-toggle="filters" aria-expanded="false">Filters</button>';
        $html .= '<button type="button" data-grid-panel-toggle="columns" aria-expanded="false">Columns</button>';
        $html .= '<button type="button" data-grid-save-view>Save view</button>';
        $html .= '<button type="button" data-grid-export>Export CSV</button>';
        $html .= '</div>';
        $html .= '<form method="get" action="' . $this->escape($formAction) . '" data-grid-state-form>';
        $html .= $this->renderFilters($definition, $state->criteria->filters);
        $html .= $this->renderColumns($definition, $state->visibleColumns, $state->columnOrder);
        $html .= '<div class="grid-workspace__actions"><button type="submit">Apply</button>';
        $html .= '<a href="' . $this->escape($formAction) . '">Reset</a></div>';
        $html .= '</form></section>';

        return $html;
    }

    private function renderViewSelector(GridViewState $state): string
    {
        $html = '<label>View <select name="bookmark_id" data-grid-view-selector>';
        $html .= '<option value="">Default view</option>';
        foreach ($state->bookmarks as $bookmark) {
            $html .= '<option value="' . (int) $bookmark['id'] . '"';
            if ($state->activeBookmarkId === (int) $bookmark['id']) {
                $html .= ' selected';
            }
            $html .= '>' . $this->escape($bookmark['name']);
            if ($bookmark['is_default']) {
                $html .= ' (Default)';
            }
            $html .= '</option>';
        }
        return $html . '</select></label>';
    }

    /** @param array<string, mixed> $values */
    private function renderFilters(GridDefinition $definition, array $values): string
    {
        $html = '<fieldset hidden data-grid-panel="filters"><legend>Filters</legend>';
        foreach ($definition->filters as $filter) {
            $html .= '<div class="grid-workspace__field">';
            $html .= '<label for="grid-filter-' . $this->escape($filter->key) . '">'
                . $this->escape($filter->label) . '</label>';
            $html .= $this->renderFilterControl($filter, $values[$filter->key] ?? null);
            $html .= '</div>';
        }
        return $html . '</fieldset>';
    }

    private function renderFilterControl(GridFilter $filter, mixed $value): string
    {
        $id = 'grid-filter-' . $this->escape($filter->key);
        if (in_array($filter->type, ['text', 'date'], true)) {
            return '<input type="' . $filter->type . '" id="' . $id . '" name="'
                . $this->escape($filter->key) . '" value="'
                . $this->escape((string) $value) . '">';
        }

        $multiple = $filter->type === 'multiselect';
        $selected = $multiple
            ? array_fill_keys(\Zoosper\Grid\GridFilterValue::many($value), true)
            : [(string) $value => true];
        $html = '<select id="' . $id . '" name="' . $this->escape($filter->key)
            . ($multiple ? '[]" multiple' : '"') . '>';
        if (!$multiple) {
            $html .= '<option value="">All</option>';
        }
        foreach ($filter->normalisedOptions() as $option) {
            $html .= '<option value="' . $this->escape($option->value) . '"'
                . (isset($selected[$option->value]) ? ' selected' : '') . '>'
                . $this->escape($option->label) . '</option>';
        }
        return $html . '</select>';
    }

    /** @param list<string> $visible @param list<string> $order */
    private function renderColumns(GridDefinition $definition, array $visible, array $order): string
    {
        $columns = [];
        foreach ($definition->columns as $column) {
            $columns[$column->key] = $column;
        }
        $html = '<fieldset hidden data-grid-panel="columns"><legend>Columns</legend>';
        $html .= '<p>Drag to reorder, or use Move up and Move down.</p><ol data-grid-column-list>';
        foreach ($order as $key) {
            if (!isset($columns[$key])) {
                continue;
            }
            $column = $columns[$key];
            $html .= '<li draggable="true" data-column-key="' . $this->escape($key) . '">';
            $html .= '<span class="grid-workspace__drag" aria-hidden="true">⋮⋮</span>';
            $html .= '<label><input type="checkbox" name="visible_columns[]" value="'
                . $this->escape($key) . '"'
                . (in_array($key, $visible, true) ? ' checked' : '')
                . (!$column->toggleable ? ' disabled' : '') . '> '
                . $this->escape($column->label) . '</label>';
            if (!$column->toggleable) {
                $html .= '<input type="hidden" name="visible_columns[]" value="' . $this->escape($key) . '">';
            }
            $html .= '<input type="hidden" name="column_order[]" value="' . $this->escape($key) . '">';
            $html .= '<button type="button" data-column-move="up">Move up</button>';
            $html .= '<button type="button" data-column-move="down">Move down</button></li>';
        }
        return $html . '</ol></fieldset>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
