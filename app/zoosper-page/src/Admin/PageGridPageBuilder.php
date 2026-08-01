<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\AdminGrid\GridWorkspaceCsrf;
use Zoosper\AdminGrid\GridWorkspacePage;
use Zoosper\AdminGrid\GridWorkspacePageRenderer;
use Zoosper\AdminGrid\GridWorkspaceRequest;

/** Builds the complete Pages workspace and matching paginated Grid. */
final readonly class PageGridPageBuilder
{
    public const MUTATION_PATH = '/admin/pages/grid';

    public function __construct(
        private PageGridHttpCoordinator $coordinator,
        private PageGridDataSource $dataSource,
        private GridWorkspacePageRenderer $renderer,
    ) {
    }

    public function build(
        int $authenticatedAdminUserId,
        GridWorkspaceRequest $request,
        GridWorkspaceCsrf $csrf,
    ): GridWorkspacePage {
        $resolved = $this->coordinator->view(
            $authenticatedAdminUserId,
            $request,
        );
        $state = $resolved['state'];
        $result = $this->dataSource->paginate($state->criteria);

        return $this->renderer->render(
            state: $state,
            result: $result,
            workspaceHtml: $resolved['html'],
            baseUrl: PageGridWorkspace::ACTION,
            mutationUrl: self::MUTATION_PATH,
            csrf: $csrf,
        );
    }
}
