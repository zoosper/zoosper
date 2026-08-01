<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

use Zoosper\Core\Pagination\PaginationResult;
use Zoosper\Grid\GridHtmlRenderer;

/** Composes resolved workspace controls and the matching Grid result. */
final readonly class GridWorkspacePageRenderer
{
    public function __construct(
        private GridWorkspaceMutationFormsRenderer $mutations,
        private GridHtmlRenderer $grid,
    ) {
    }

    public function render(
        GridViewState $state,
        PaginationResult $result,
        string $workspaceHtml,
        string $baseUrl,
        string $mutationUrl,
        GridWorkspaceCsrf $csrf,
    ): GridWorkspacePage {
        $workspace = $workspaceHtml . $this->mutations->render(
            $state,
            $mutationUrl,
            $csrf->field,
            $csrf->token,
        );
        $grid = $this->grid->render(
            $state->definition,
            $result,
            $state->criteria,
            $baseUrl,
        );

        return new GridWorkspacePage($state, $workspace, $grid);
    }
}
