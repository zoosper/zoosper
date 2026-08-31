<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Renders accessible navigation without rebuilding query state. */
final readonly class GridWorkspaceNavigationRenderer
{
    public function render(GridWorkspaceNavigation $navigation): string
    {
        $html = '<nav class="grid-workspace__navigation" aria-label="Grid navigation">';
        if ($navigation->previousUrl !== null) {
            $html .= '<a rel="prev" href="' . $this->escape($navigation->previousUrl)
                . '">Previous</a>';
        }
        if ($navigation->nextUrl !== null) {
            $html .= '<a rel="next" href="' . $this->escape($navigation->nextUrl)
                . '">Next</a>';
        }
        $html .= '<a data-grid-export href="' . $this->escape($navigation->exportUrl)
            . '">Export CSV</a>';

        return $html . '</nav>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}











