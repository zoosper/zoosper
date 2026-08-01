<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

use Zoosper\Grid\GridHtmlRenderer;

/** Builds the complete Roles Grid page without changing role or ACL writes. */
final readonly class RoleGridPageBuilder
{
    public function __construct(
        private RoleGridWorkspace $workspace,
        private RoleGridDataSource $dataSource,
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
            title: 'Roles & Permissions',
            workspaceHtml: $resolved['html'],
            gridHtml: $this->renderer->renderBody(
                $state->definition,
                $pagination,
                $state->criteria,
                RoleGridWorkspace::ACTION,
            ),
            state: $state,
            pagination: $pagination,
        );
    }
}
