<?php

declare(strict_types=1);

namespace Zoosper\Grid;

/** Renders an accessible, escaped multi-select control for a GridFilter. */
final readonly class GridMultiselectRenderer
{
    /** @param list<string> $selectedValues */
    public function render(GridFilter $filter, array $selectedValues = []): string
    {
        if ($filter->type !== 'multiselect') {
            throw new \InvalidArgumentException(
                'GridMultiselectRenderer requires a multiselect filter; received: ' . $filter->type,
            );
        }

        $selected = array_fill_keys(GridFilterValue::many($selectedValues), true);
        $id = 'grid-filter-' . preg_replace('/[^a-zA-Z0-9_-]+/', '-', $filter->key);
        $html = '<label for="' . $this->escape($id) . '">'
            . $this->escape($filter->label)
            . '</label>';
        $html .= '<select id="' . $this->escape($id) . '" name="'
            . $this->escape($filter->key) . '[]" multiple>';

        foreach ($filter->normalisedOptions() as $option) {
            $html .= '<option value="' . $this->escape($option->value) . '"';
            if (isset($selected[$option->value])) {
                $html .= ' selected';
            }
            $html .= '>' . $this->escape($option->label) . '</option>';
        }

        return $html . '</select>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
