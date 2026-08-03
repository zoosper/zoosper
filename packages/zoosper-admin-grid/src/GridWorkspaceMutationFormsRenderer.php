<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/**
 * Renders CSRF-bearing POST controls for Grid workspace persistence.
 *
 * A single named-view form owns the view name and exposes two submit buttons.
 * The selected action is carried by the clicked button, so Save view and
 * Save as default view share one input without JavaScript.
 */
final readonly class GridWorkspaceMutationFormsRenderer
{
    public function render(
        GridViewState $state,
        string $actionPath,
        string $csrfField,
        string $csrfToken,
    ): string {
        $this->assertLocalPath($actionPath);
        if (trim($csrfField) === '' || trim($csrfToken) === '') {
            throw new \InvalidArgumentException(
                'Grid workspace mutation forms require a CSRF field and token.',
            );
        }

        $html = '<section class="grid-workspace__mutations" data-grid-mutations aria-label="Saved Grid settings">';
        $html .= '<div class="grid-workspace__mutations-title"><strong>Saved Grid settings</strong>';
        $html .= '<span>Persist columns or save this workspace as a named view.</span></div>';
        $html .= '<div class="grid-workspace__column-actions" aria-label="Column preferences">';
        $html .= $this->saveColumnsForm($state, $actionPath, $csrfField, $csrfToken);
        $html .= $this->resetColumnsForm($actionPath, $csrfField, $csrfToken);
        $html .= '</div>';
        $html .= $this->saveViewForm($state, $actionPath, $csrfField, $csrfToken);

        if ($state->activeBookmarkId !== null) {
            $html .= $this->deleteViewForm(
                $state->activeBookmarkId,
                $actionPath,
                $csrfField,
                $csrfToken,
            );
        }

        return $html . '</section>';
    }

    private function saveColumnsForm(
        GridViewState $state,
        string $path,
        string $csrfField,
        string $csrfToken,
    ): string {
        $html = $this->openForm($path, $csrfField, $csrfToken);
        foreach ($state->visibleColumns as $key) {
            $html .= $this->hidden('visible_columns[]', $key);
        }
        foreach ($state->columnOrder as $key) {
            $html .= $this->hidden('column_order[]', $key);
        }

        return $html . '<button type="submit" name="action" value="'
            . GridWorkspaceMutationContract::SAVE_COLUMNS . '">Save columns</button></form>';
    }

    private function resetColumnsForm(
        string $path,
        string $csrfField,
        string $csrfToken,
    ): string {
        return $this->openForm($path, $csrfField, $csrfToken)
            . '<button type="submit" name="action" value="'
            . GridWorkspaceMutationContract::RESET_COLUMNS
            . '" class="is-secondary">Reset columns</button></form>';
    }

    private function saveViewForm(
        GridViewState $state,
        string $path,
        string $csrfField,
        string $csrfToken,
    ): string {
        $html = $this->openForm($path, $csrfField, $csrfToken, 'grid-workspace__mutation-form grid-workspace__named-view-form');
        $html .= '<label>View name <input name="view_name" maxlength="120" required autocomplete="off"></label>';
        foreach ($state->criteria->filters as $key => $value) {
            foreach (is_array($value) ? $value : [$value] as $item) {
                $html .= $this->hidden('filters[' . $key . '][]', (string) $item);
            }
        }
        $html .= $this->hidden('sort_by', (string) ($state->criteria->sortBy ?? ''));
        $html .= $this->hidden('sort_dir', $state->criteria->sortDir);
        $html .= $this->hidden('page_size', (string) $state->criteria->pager->pageSize);
        foreach ($state->visibleColumns as $key) {
            $html .= $this->hidden('visible_columns[]', $key);
        }
        foreach ($state->columnOrder as $key) {
            $html .= $this->hidden('column_order[]', $key);
        }
        $html .= '<div class="grid-workspace__named-view-actions">';
        $html .= '<button type="submit" name="action" value="'
            . GridWorkspaceMutationContract::SAVE_VIEW . '">Save view</button>';
        $html .= '<button type="submit" name="action" value="'
            . GridWorkspaceMutationContract::SET_DEFAULT_VIEW
            . '" class="is-secondary">Save &amp; make default</button>';

        return $html . '</div></form>';
    }

    private function deleteViewForm(
        int $bookmarkId,
        string $path,
        string $csrfField,
        string $csrfToken,
    ): string {
        return $this->openForm($path, $csrfField, $csrfToken)
            . $this->hidden('bookmark_id', (string) $bookmarkId)
            . '<button type="submit" name="action" value="'
            . GridWorkspaceMutationContract::DELETE_VIEW
            . '" class="is-danger">Delete view</button></form>';
    }

    private function openForm(
        string $path,
        string $csrfField,
        string $csrfToken,
        string $class = 'grid-workspace__mutation-form',
    ): string {
        return '<form class="' . $this->escape($class) . '" method="post" action="'
            . $this->escape($path) . '">' . $this->hidden($csrfField, $csrfToken);
    }

    private function hidden(string $name, string $value): string
    {
        return '<input type="hidden" name="' . $this->escape($name)
            . '" value="' . $this->escape($value) . '">';
    }

    private function assertLocalPath(string $path): void
    {
        if ($path === '' || $path[0] !== '/' || str_contains($path, '://')) {
            throw new \InvalidArgumentException(
                'Grid workspace form action must use an application-local path.',
            );
        }
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
