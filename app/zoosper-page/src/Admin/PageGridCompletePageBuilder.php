<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\AdminGrid\GridWorkspaceCompletePage;
use Zoosper\AdminGrid\GridWorkspaceCompletePageRenderer;
use Zoosper\AdminGrid\GridWorkspaceCsrf;
use Zoosper\AdminGrid\GridWorkspacePagination;
use Zoosper\AdminGrid\GridWorkspaceRequest;

/** Builds the complete Pages screen from one resolved view and pagination result. */
final readonly class PageGridCompletePageBuilder
{
    public function __construct(
        private PageGridPageBuilder $page,
        private PageGridNavigationBuilder $navigation,
        private GridWorkspaceCompletePageRenderer $renderer,
    ) {
    }

    public function build(
        int $authenticatedAdminUserId,
        GridWorkspaceRequest $request,
        GridWorkspaceCsrf $csrf,
        GridWorkspacePagination $pagination,
    ): GridWorkspaceCompletePage {
        $page = $this->page->build($authenticatedAdminUserId, $request, $csrf);
        $navigation = $this->navigation->build(
            $page->state,
            $pagination->currentPage,
            $pagination->totalPages,
        );

        return $this->renderer->render($page, $navigation);
    }
}
