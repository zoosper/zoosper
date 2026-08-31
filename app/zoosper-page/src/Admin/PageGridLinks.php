<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\AdminGrid\GridViewState;
use Zoosper\AdminGrid\GridWorkspaceQuery;
use Zoosper\Core\Url\AdminUrlGenerator;

/** Page-owned URLs for view navigation and resolved-view CSV export. */
final readonly class PageGridLinks
{
    public function __construct(
        private GridWorkspaceQuery $query,
        private ?AdminUrlGenerator $adminUrls = null,
    )
    {
    }

    public function page(GridViewState $state, int $page): string
    {
        return $this->query->url($this->adminUrls?->url('pages') ?? PageGridEndpointContract::VIEW_PATH, $state, page: $page);
    }

    public function sort(GridViewState $state, string $column, string $direction): string
    {
        return $this->query->url(
            $this->adminUrls?->url('pages') ?? PageGridEndpointContract::VIEW_PATH,
            $state,
            page: 1,
            sortBy: $column,
            sortDir: $direction,
        );
    }

    public function export(GridViewState $state): string
    {
        return $this->query->url($this->adminUrls?->url('pages/export') ?? '/admin/pages/export', $state);
    }
}










