<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Resolves the active label and dirty state without trusting browser flags. */
final readonly class GridWorkspaceViewStatusResolver
{
    public function __construct(
        private GridWorkspaceActiveBookmark $bookmarks,
        private GridWorkspaceDirtyState $dirty,
    ) {
    }

    public function resolve(GridViewState $state): GridWorkspaceViewStatus
    {
        $bookmark = $this->bookmarks->find($state);
        if ($bookmark === null) {
            return new GridWorkspaceViewStatus('Default view', false, false);
        }

        return new GridWorkspaceViewStatus(
            label: $bookmark['name'],
            isSavedView: true,
            isDirty: $this->dirty->isDirty($state, $bookmark['state']),
        );
    }
}
