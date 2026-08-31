<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Resolves save/delete/default actions from user-scoped bookmark state. */
final readonly class GridWorkspaceViewActionsResolver
{
    public function __construct(
        private GridWorkspaceActiveBookmark $bookmarks,
        private GridWorkspaceDirtyState $dirty,
    ) {
    }

    public function resolve(GridViewState $state): GridWorkspaceViewActions
    {
        $bookmark = $this->bookmarks->find($state);
        if ($bookmark === null) {
            return new GridWorkspaceViewActions('', false, false, false, null);
        }

        return new GridWorkspaceViewActions(
            viewName: $bookmark['name'],
            hasActiveView: true,
            isDefault: $bookmark['is_default'],
            isDirty: $this->dirty->isDirty($state, $bookmark['state']),
            bookmarkId: $bookmark['id'],
        );
    }
}











