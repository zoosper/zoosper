<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

use Zoosper\Grid\GridColumnOrderer;
use Zoosper\Grid\GridDefinition;

final readonly class GridViewStateResolver
{
    public function __construct(
        private GridPreferenceRepository $preferences,
        private GridBookmarkRepository $bookmarks,
        private GridStateNormaliser $normaliser,
        private GridColumnOrderer $columnOrderer,
    ) {
    }

    /** @param array<string, mixed> $queryState */
    public function resolve(
        int $adminUserId,
        string $gridKey,
        GridDefinition $definition,
        array $queryState = [],
        ?int $bookmarkId = null,
    ): GridViewState {
        $bookmarks = $this->bookmarks->allForUser($adminUserId, $gridKey);
        $bookmark = $this->selectBookmark($bookmarks, $bookmarkId);
        $savedColumns = $this->preferences->findVisibleColumns($adminUserId, $gridKey);
        $state = array_replace_recursive($bookmark['state'] ?? [], $queryState);
        if ($savedColumns !== null && !array_key_exists('visible_columns', $state)) {
            $state['visible_columns'] = $savedColumns;
        }

        $normalised = $this->normaliser->normalise($state, $definition);
        $ordered = $this->columnOrderer->apply($definition, $normalised['column_order']);
        $visibleDefinition = $ordered->withVisibleColumnKeys($normalised['visible_columns']);

        return new GridViewState(
            definition: $visibleDefinition,
            criteria: $this->normaliser->criteria($normalised, $definition),
            visibleColumns: $normalised['visible_columns'],
            columnOrder: $normalised['column_order'],
            bookmarks: $bookmarks,
            activeBookmarkId: isset($bookmark['id']) ? (int) $bookmark['id'] : null,
        );
    }

    /**
     * @param list<array{id: int, name: string, state: array<string, mixed>, is_default: bool}> $bookmarks
     * @return array{id: int, name: string, state: array<string, mixed>, is_default: bool}|null
     */
    private function selectBookmark(array $bookmarks, ?int $bookmarkId): ?array
    {
        if ($bookmarkId !== null) {
            foreach ($bookmarks as $bookmark) {
                if ($bookmark['id'] === $bookmarkId) {
                    return $bookmark;
                }
            }
            return null;
        }
        foreach ($bookmarks as $bookmark) {
            if ($bookmark['is_default']) {
                return $bookmark;
            }
        }
        return null;
    }
}
