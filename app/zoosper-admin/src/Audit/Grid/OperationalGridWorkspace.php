<?php

declare(strict_types=1);

namespace Zoosper\Admin\Audit\Grid;

use Zoosper\AdminGrid\GridCompactWorkspaceRenderer;
use Zoosper\AdminGrid\GridViewState;
use Zoosper\AdminGrid\GridViewStateResolver;
use Zoosper\Grid\GridColumnOrderer;
use Zoosper\Grid\GridDefinition;

/**
 * Shared Auth-owned integration seam for a resolved Admin Grid workspace.
 *
 * The table keeps the visibility-filtered state returned by the resolver, while
 * the controls render from the complete ordered definition so hidden columns
 * remain recoverable.
 */
final readonly class OperationalGridWorkspace
{
    public function __construct(
        private GridViewStateResolver $resolver,
        private GridCompactWorkspaceRenderer $renderer,
        private ?GridColumnOrderer $columnOrderer = null,
    ) {
    }

    /**
     * @param array<string, mixed> $queryState
     * @return array{state: GridViewState, html: string}
     */
    public function resolve(
        int $adminUserId,
        string $gridKey,
        string $action,
        GridDefinition $definition,
        array $queryState,
        ?int $bookmarkId = null,
    ): array {
        $state = $this->resolver->resolve(
            adminUserId: $adminUserId,
            gridKey: $gridKey,
            definition: $definition,
            queryState: $queryState,
            bookmarkId: $bookmarkId,
        );

        $orderedDefinition = ($this->columnOrderer ?? new GridColumnOrderer())
            ->apply($definition, $state->columnOrder);

        $controlState = new GridViewState(
            definition: $orderedDefinition,
            criteria: $state->criteria,
            visibleColumns: $state->visibleColumns,
            columnOrder: $state->columnOrder,
            bookmarks: $state->bookmarks,
            activeBookmarkId: $state->activeBookmarkId,
        );

        return [
            'state' => $state,
            'html' => $this->renderer->render($controlState, $action),
        ];
    }
}
