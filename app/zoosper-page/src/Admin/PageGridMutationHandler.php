<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use InvalidArgumentException;
use Zoosper\AdminGrid\GridViewMutationService;
use Zoosper\AdminGrid\GridWorkspaceMutationContract;

/**
 * Pages-owned dispatcher for already-authenticated and CSRF-validated POSTs.
 */
final readonly class PageGridMutationHandler
{
    public function __construct(
        private PageGridDefinition $definition,
        private GridViewMutationService $mutations,
    ) {
    }

    /**
     * @param array<string, mixed> $post
     */
    public function handle(int $adminUserId, string $action, array $post): void
    {
        $definition = $this->definition->build();

        match ($action) {
            GridWorkspaceMutationContract::SAVE_COLUMNS => $this->mutations->saveColumnPreferences(
                $adminUserId,
                PageGridWorkspace::GRID_KEY,
                $definition,
                is_array($post['visible_columns'] ?? null) ? $post['visible_columns'] : [],
                is_array($post['column_order'] ?? null) ? $post['column_order'] : [],
            ),
            GridWorkspaceMutationContract::RESET_COLUMNS => $this->mutations->resetVisibleColumns(
                $adminUserId,
                PageGridWorkspace::GRID_KEY,
            ),
            GridWorkspaceMutationContract::SAVE_VIEW,
            GridWorkspaceMutationContract::SET_DEFAULT_VIEW => $this->mutations->saveBookmark(
                $adminUserId,
                PageGridWorkspace::GRID_KEY,
                $definition,
                (string) ($post['view_name'] ?? ''),
                self::submittedState($post),
                $action === GridWorkspaceMutationContract::SET_DEFAULT_VIEW,
            ),
            GridWorkspaceMutationContract::DELETE_VIEW => $this->mutations->deleteBookmark(
                $adminUserId,
                PageGridWorkspace::GRID_KEY,
                (int) ($post['bookmark_id'] ?? 0),
            ),
            default => throw new InvalidArgumentException('Unsupported Pages grid action: ' . $action),
        };
    }

    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    private static function submittedState(array $post): array
    {
        return [
            'filters' => is_array($post['filters'] ?? null) ? $post['filters'] : [],
            'sort_by' => (string) ($post['sort_by'] ?? ''),
            'sort_dir' => (string) ($post['sort_dir'] ?? 'desc'),
            'page_size' => (int) ($post['page_size'] ?? 20),
            'visible_columns' => is_array($post['visible_columns'] ?? null) ? $post['visible_columns'] : [],
            'column_order' => is_array($post['column_order'] ?? null) ? $post['column_order'] : [],
        ];
    }
}










