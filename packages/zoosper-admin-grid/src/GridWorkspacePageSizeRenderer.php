<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Renders the GET-based Items per page selector. */
final readonly class GridWorkspacePageSizeRenderer
{
    public function __construct(private GridWorkspacePageSizeOptions $options)
    {
    }

    public function render(int $selected): string
    {
        $selected = $this->options->normalise($selected);
        $html = '<label class="grid-workspace__page-size">Items per page '
            . '<select name="page_size" data-grid-page-size>';

        foreach ($this->options->values as $value) {
            $html .= '<option value="' . $value . '"'
                . ($value === $selected ? ' selected' : '')
                . '>' . $value . '</option>';
        }

        return $html . '</select></label>';
    }
}
