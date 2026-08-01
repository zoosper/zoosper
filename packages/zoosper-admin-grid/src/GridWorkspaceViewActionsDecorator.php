<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Inserts view actions immediately after the active-view status. */
final readonly class GridWorkspaceViewActionsDecorator
{
    public function __construct(
        private GridWorkspaceViewActionsResolver $actions,
        private GridWorkspaceViewActionsRenderer $renderer,
    ) {
    }

    public function decorate(GridViewState $state, string $workspaceHtml): string
    {
        $marker = '</div>';
        $statusPosition = strpos($workspaceHtml, 'data-grid-view-status');
        if ($statusPosition === false) {
            throw new \RuntimeException('Unable to decorate Grid workspace: view status was not found.');
        }
        $insertAt = strpos($workspaceHtml, $marker, $statusPosition);
        if ($insertAt === false) {
            throw new \RuntimeException('Unable to decorate Grid workspace: view status is malformed.');
        }
        $insertAt += strlen($marker);

        return substr($workspaceHtml, 0, $insertAt)
            . $this->renderer->render($this->actions->resolve($state))
            . substr($workspaceHtml, $insertAt);
    }
}
