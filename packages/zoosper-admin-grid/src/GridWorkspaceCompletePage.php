<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Complete server-rendered Grid workspace with rows and navigation. */
final readonly class GridWorkspaceCompletePage
{
    public function __construct(
        public GridViewState $state,
        public string $workspaceHtml,
        public string $gridHtml,
        public string $navigationHtml,
    ) {
    }

    public function html(): string
    {
        return $this->workspaceHtml . $this->gridHtml . $this->navigationHtml;
    }
}
