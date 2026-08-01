<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/**
 * Injects the server-resolved active-view status into workspace markup.
 *
 * The base renderer remains reusable and the decorator fails closed when the
 * expected toolbar marker is unavailable rather than duplicating the workspace.
 */
final readonly class GridWorkspaceStatusDecorator
{
    public function __construct(
        private GridWorkspaceViewStatusResolver $status,
        private GridWorkspaceViewStatusRenderer $renderer,
    ) {
    }

    public function decorate(GridViewState $state, string $workspaceHtml): string
    {
        $statusHtml = $this->renderer->render($this->status->resolve($state));
        $marker = '<div class="grid-workspace__bar">';
        $position = strpos($workspaceHtml, $marker);

        if ($position === false) {
            throw new \RuntimeException(
                'Unable to decorate Grid workspace: toolbar marker was not found.',
            );
        }

        $insertAt = $position + strlen($marker);

        return substr($workspaceHtml, 0, $insertAt)
            . $statusHtml
            . substr($workspaceHtml, $insertAt);
    }
}
