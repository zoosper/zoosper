<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Admin;

use Zoosper\AdminGrid\GridCompactWorkspaceRenderer;
use Zoosper\AdminGrid\GridViewState;
use Zoosper\AdminGrid\GridViewStateResolver;
use Zoosper\Grid\GridColumnOrderer;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\StoreOrders\StoreOrderGrid;

/** Store Orders integration seam for the shared per-admin Grid workspace. */
final readonly class StoreOrderGridWorkspace
{
    public const GRID_KEY = 'store.orders';
    public const ACTION = '/admin/store-orders';

    public function __construct(
        private GridViewStateResolver $resolver,
        private GridCompactWorkspaceRenderer $renderer = new GridCompactWorkspaceRenderer(),
        private GridColumnOrderer $columnOrderer = new GridColumnOrderer(),
        private ?AdminUrlGenerator $adminUrls = null,
    ) {
    }

    public function action(): string
    {
        return $this->adminUrls?->url('store-orders') ?? self::ACTION;
    }

    public function exportUrl(): string
    {
        return $this->adminUrls?->url('store-orders/export') ?? self::ACTION . '/export';
    }

    /**
     * @param array<string, mixed> $queryState
     * @return array{state: GridViewState, html: string}
     */
    public function resolve(int $adminUserId, array $queryState, ?int $bookmarkId = null): array
    {
        $apiDefinition = StoreOrderGrid::definition();
        $definition = $apiDefinition->grid;
        $state = $this->resolver->resolve(
            adminUserId: $adminUserId,
            gridKey: self::GRID_KEY,
            definition: $definition,
            queryState: $queryState,
            bookmarkId: $bookmarkId,
        );

        // The table receives the visibility-filtered definition. The chooser
        // must receive all safe declared columns so hidden columns stay recoverable.
        $workspaceState = new GridViewState(
            definition: $this->columnOrderer->apply($definition, $state->columnOrder),
            criteria: $state->criteria,
            visibleColumns: $state->visibleColumns,
            columnOrder: $state->columnOrder,
            bookmarks: $state->bookmarks,
            activeBookmarkId: $state->activeBookmarkId,
        );

        return [
            'state' => $state,
            'html' => $this->renderer->render(
                $workspaceState,
                $this->action(),
                $this->exportUrl(),
                pageSizeOptions: $apiDefinition->pageSizes,
            ),
        ];
    }
}
