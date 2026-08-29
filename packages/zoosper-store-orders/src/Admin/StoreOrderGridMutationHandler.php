<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Admin;

use InvalidArgumentException;
use Zoosper\AdminGrid\GridViewMutationService;
use Zoosper\AdminGrid\GridWorkspaceMutationContract;
use Zoosper\AdminGrid\GridWorkspacePostState;
use Zoosper\StoreOrders\StoreOrderGrid;

/** Store Orders-owned dispatcher for authenticated and CSRF-validated mutations. */
final readonly class StoreOrderGridMutationHandler
{
    public function __construct(private GridViewMutationService $mutations) {}

    /** @param array<string, mixed> $post */
    public function handle(int $adminUserId, string $action, array $post): void
    {
        $definition = StoreOrderGrid::definition()->grid;
        match ($action) {
            GridWorkspaceMutationContract::SAVE_COLUMNS => $this->mutations->saveColumnPreferences(
                $adminUserId, StoreOrderGridWorkspace::GRID_KEY, $definition,
                is_array($post['visible_columns'] ?? null) ? $post['visible_columns'] : [],
                is_array($post['column_order'] ?? null) ? $post['column_order'] : [],
            ),
            GridWorkspaceMutationContract::RESET_COLUMNS => $this->mutations->resetVisibleColumns(
                $adminUserId, StoreOrderGridWorkspace::GRID_KEY,
            ),
            GridWorkspaceMutationContract::SAVE_VIEW,
            GridWorkspaceMutationContract::SET_DEFAULT_VIEW => $this->mutations->saveBookmark(
                $adminUserId, StoreOrderGridWorkspace::GRID_KEY, $definition,
                (string) ($post['view_name'] ?? ''),
                GridWorkspacePostState::fromPost($post),
                $action === GridWorkspaceMutationContract::SET_DEFAULT_VIEW,
            ),
            GridWorkspaceMutationContract::DELETE_VIEW => $this->mutations->deleteBookmark(
                $adminUserId, StoreOrderGridWorkspace::GRID_KEY, (int) ($post['bookmark_id'] ?? 0),
            ),
            default => throw new InvalidArgumentException('Unsupported Store Orders grid action: ' . $action),
        };
    }
}
