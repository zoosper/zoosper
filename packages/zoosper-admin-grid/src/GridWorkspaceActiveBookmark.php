<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Extracts the active bookmark metadata already resolved for the current user. */
final readonly class GridWorkspaceActiveBookmark
{
    /** @return array{id: int, name: string, state: array<string, mixed>, is_default: bool}|null */
    public function find(GridViewState $state): ?array
    {
        if ($state->activeBookmarkId === null) {
            return null;
        }

        foreach ($state->bookmarks as $bookmark) {
            if ((int) ($bookmark['id'] ?? 0) !== $state->activeBookmarkId) {
                continue;
            }

            return [
                'id' => $state->activeBookmarkId,
                'name' => (string) ($bookmark['name'] ?? 'Saved view'),
                'state' => is_array($bookmark['state'] ?? null) ? $bookmark['state'] : [],
                'is_default' => (bool) ($bookmark['is_default'] ?? false),
            ];
        }

        return null;
    }
}











