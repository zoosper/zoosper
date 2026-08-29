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
        $savedPreferences = $this->preferences->findColumnPreferences($adminUserId, $gridKey);
        $state = array_replace($bookmark['state'] ?? [], $queryState);
        if ($savedPreferences !== null && !array_key_exists('visible_columns', $state)) {
            $state['visible_columns'] = $savedPreferences['visible_columns'];
        }
        if (
            $savedPreferences !== null
            && $savedPreferences['column_order'] !== []
            && !array_key_exists('column_order', $state)
        ) {
            $state['column_order'] = $savedPreferences['column_order'];
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
