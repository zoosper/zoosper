<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

use Zoosper\Grid\GridHtmlRenderer;

/** Builds the complete Admin Users Grid page without changing CRUD writes. */
final readonly class AdminUserGridPageBuilder
{
    public function __construct(
        private AdminUserGridWorkspace $workspace,
        private AdminUserGridDataSource $dataSource,
        private GridHtmlRenderer $renderer,
    ) {
    }

    /** @param array<string, mixed> $queryState */
    public function build(
        int $authenticatedAdminUserId,
        array $queryState,
        ?int $bookmarkId = null,
    ): AuthGridPage {
        $resolved = $this->workspace->resolve(
            $authenticatedAdminUserId,
            $queryState,
            $bookmarkId,
        );
        $state = $resolved['state'];
        $pagination = $this->dataSource->paginate($state->criteria);

        return new AuthGridPage(
            title: 'Admin Users',
            workspaceHtml: $resolved['html'],
            gridHtml: $this->renderer->renderBody(
                $state->definition,
                $pagination,
                $state->criteria,
                AdminUserGridWorkspace::ACTION,
            ),
            state: $state,
            pagination: $pagination,
        );
    }
}
