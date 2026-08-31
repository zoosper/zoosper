<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Inserts page-size selection into the existing Grid workspace GET toolbar. */
final readonly class GridWorkspacePageSizeDecorator
{
    public function __construct(private GridWorkspacePageSizeRenderer $renderer)
    {
    }

    public function decorate(GridViewState $state, string $workspaceHtml): string
    {
        $marker = '</form>';
        $position = strpos($workspaceHtml, $marker);
        if ($position === false) {
            throw new \RuntimeException(
                'Unable to decorate Grid workspace: GET form closing tag was not found.',
            );
        }

        return substr($workspaceHtml, 0, $position)
            . $this->renderer->render($state->criteria->pager->pageSize)
            . substr($workspaceHtml, $position);
    }
}











