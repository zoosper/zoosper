<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Fully rendered workspace and grid content for a feature controller. */
final readonly class GridWorkspacePage
{
    public function __construct(
        public GridViewState $state,
        public string $workspaceHtml,
        public string $gridHtml,
    ) {
    }

    public function html(): string
    {
        return $this->workspaceHtml . $this->gridHtml;
    }
}











