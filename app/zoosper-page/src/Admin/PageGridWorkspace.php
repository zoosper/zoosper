<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\AdminGrid\GridViewState;
use Zoosper\AdminGrid\GridViewStateResolver;
use Zoosper\AdminGrid\GridWorkspaceRenderer;
use Zoosper\Grid\GridDefinition;

/**
 * Pages-specific integration seam for the shared Admin Grid workspace.
 *
 * The controller supplies the authenticated admin ID and request state. The
 * grid key and URL remain server-owned constants.
 */
final readonly class PageGridWorkspace
{
    public const GRID_KEY = 'admin.pages';
    public const ACTION = '/admin/pages';

    public function __construct(
        private PageGridDefinition $definition,
        private GridViewStateResolver $resolver,
        private GridWorkspaceRenderer $renderer,
    ) {
    }

    /**
     * @param array<string, mixed> $queryState
     * @return array{state: GridViewState, html: string}
     */
    public function resolve(
        int $adminUserId,
        array $queryState,
        ?int $bookmarkId = null,
    ): array {
        $state = $this->resolver->resolve(
            adminUserId: $adminUserId,
            gridKey: self::GRID_KEY,
            definition: $this->definition->build(),
            queryState: $queryState,
            bookmarkId: $bookmarkId,
        );

        return [
            'state' => $state,
            'html' => $this->renderer->render($state, self::ACTION),
        ];
    }
}
