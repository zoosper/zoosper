<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Renders the active view label and server-authoritative dirty-state indicator. */
final readonly class GridWorkspaceViewStatusRenderer
{
    public function render(GridWorkspaceViewStatus $status): string
    {
        $html = '<div class="grid-workspace__view-status" data-grid-view-status>';
        $html .= '<span class="grid-workspace__view-label">'
            . $this->escape($status->label) . '</span>';

        if ($status->isDirty) {
            $html .= '<span class="grid-workspace__dirty" role="status"'
                . ' aria-label="The selected view has unsaved changes">'
                . 'Unsaved changes</span>';
        }

        return $html . '</div>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}











