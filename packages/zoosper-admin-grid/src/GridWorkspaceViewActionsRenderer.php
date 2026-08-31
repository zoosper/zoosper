<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/**
 * Renders accessible controls for creating, updating, defaulting and deleting
 * views. CSRF and complete state fields remain owned by mutation-form rendering.
 */
final readonly class GridWorkspaceViewActionsRenderer
{
    public function render(GridWorkspaceViewActions $actions): string
    {
        $html = '<div class="grid-workspace__view-actions" data-grid-view-actions>';
        $html .= '<label class="grid-workspace__view-name">View name '
            . '<input name="view_name" maxlength="120" autocomplete="off" value="'
            . $this->escape($actions->viewName) . '"></label>';

        $saveLabel = $actions->hasActiveView ? 'Update view' : 'Save new view';
        $html .= '<button type="submit" data-grid-view-action="save_view"';
        if ($actions->isDirty) {
            $html .= ' class="is-primary"';
        }
        $html .= '>' . $saveLabel . '</button>';

        if ($actions->hasActiveView && !$actions->isDefault) {
            $html .= '<button type="submit" data-grid-view-action="set_default_view">'
                . 'Make default</button>';
        }
        if ($actions->hasActiveView) {
            $html .= '<button type="submit" data-grid-view-action="delete_view"'
                . ' class="is-danger">Delete view</button>';
        }

        return $html . '</div>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}











