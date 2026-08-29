<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

use InvalidArgumentException;
use Zoosper\Grid\GridDefinition;

/**
 * Application service for authenticated, CSRF-checked grid mutations.
 *
 * Controllers must derive the admin user ID from the authenticated session and
 * validate CSRF before invoking this service. Client-provided user IDs are not
 * part of the contract.
 */
final readonly class GridViewMutationService
{
    public function __construct(
        private GridPreferenceRepository $preferences,
        private GridBookmarkRepository $bookmarks,
        private GridStateNormaliser $normaliser,
    ) {
    }

    /**
     * @param list<string> $visibleColumns
     * @param list<string> $columnOrder
     * @return array{visible_columns: list<string>, column_order: list<string>}
     */
    public function saveColumnPreferences(
        int $adminUserId,
        string $gridKey,
        GridDefinition $definition,
        array $visibleColumns,
        array $columnOrder,
    ): array {
        $state = $this->normaliser->normalise([
            'visible_columns' => $visibleColumns,
            'column_order' => $columnOrder,
        ], $definition);
        $this->preferences->saveColumnPreferences(
            $adminUserId,
            $gridKey,
            $state['visible_columns'],
            $state['column_order'],
        );

        return [
            'visible_columns' => $state['visible_columns'],
            'column_order' => $state['column_order'],
        ];
    }

    /**
     * Backwards-compatible visibility-only mutation contract.
     *
     * @param list<string> $visibleColumns
     * @return list<string>
     */
    public function saveVisibleColumns(
        int $adminUserId,
        string $gridKey,
        GridDefinition $definition,
        array $visibleColumns,
    ): array {
        $state = $this->saveColumnPreferences(
            $adminUserId,
            $gridKey,
            $definition,
            $visibleColumns,
            $this->preferences->findColumnOrder($adminUserId, $gridKey)
                ?? $definition->allColumnKeys(),
        );

        return $state['visible_columns'];
    }

    public function resetVisibleColumns(int $adminUserId, string $gridKey): void
    {
        $this->preferences->clear($adminUserId, $gridKey);
    }

    /**
     * @param array<string, mixed> $submittedState
     * @return array<string, mixed>
     */
    public function saveBookmark(
        int $adminUserId,
        string $gridKey,
        GridDefinition $definition,
        string $name,
        array $submittedState,
        bool $isDefault = false,
    ): array {
        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Grid view name cannot be empty.');
        }
        if (mb_strlen($name) > 120) {
            throw new InvalidArgumentException('Grid view name cannot exceed 120 characters.');
        }

        $state = $this->normaliser->normalise($submittedState, $definition);
        $this->bookmarks->save(
            $adminUserId,
            $gridKey,
            $name,
            $state,
            $isDefault,
        );

        return $state;
    }

    public function deleteBookmark(
        int $adminUserId,
        string $gridKey,
        int $bookmarkId,
    ): void {
        if ($bookmarkId <= 0) {
            throw new InvalidArgumentException('Grid bookmark ID must be a positive integer.');
        }

        $this->bookmarks->delete($adminUserId, $gridKey, $bookmarkId);
    }
}
