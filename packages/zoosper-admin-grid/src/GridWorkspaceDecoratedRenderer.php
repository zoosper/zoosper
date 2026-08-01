<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Renders the base workspace and applies shared presentation decorators. */
final readonly class GridWorkspaceDecoratedRenderer
{
    public function __construct(
        private GridWorkspaceRenderer $workspace,
        private GridWorkspaceStatusDecorator $status,
    ) {
    }

    public function render(GridViewState $state, string $formAction): string
    {
        return $this->status->decorate(
            $state,
            $this->workspace->render($state, $formAction),
        );
    }
}
