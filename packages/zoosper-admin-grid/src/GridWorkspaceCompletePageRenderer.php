<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Appends resolved navigation to an already rendered workspace page. */
final readonly class GridWorkspaceCompletePageRenderer
{
    public function __construct(private GridWorkspaceNavigationRenderer $navigation)
    {
    }

    public function render(
        GridWorkspacePage $page,
        GridWorkspaceNavigation $navigation,
    ): GridWorkspaceCompletePage {
        return new GridWorkspaceCompletePage(
            state: $page->state,
            workspaceHtml: $page->workspaceHtml,
            gridHtml: $page->gridHtml,
            navigationHtml: $this->navigation->render($navigation),
        );
    }
}
