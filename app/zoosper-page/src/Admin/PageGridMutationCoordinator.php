<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\AdminGrid\GridWorkspaceMutationGuard;
use Zoosper\AdminGrid\GridWorkspaceMutationMessages;
use Zoosper\AdminGrid\GridWorkspaceMutationResult;
use Zoosper\AdminGrid\GridWorkspaceRequest;

/**
 * Final feature-level mutation coordinator for Pages.
 *
 * Authentication, Page permission and CSRF validation must be completed by the
 * HTTP controller before this service is called.
 */
final readonly class PageGridMutationCoordinator
{
    public function __construct(
        private PageGridMutationHandler $handler,
        private GridWorkspaceMutationGuard $guard,
    ) {
    }

    public function mutate(
        int $authenticatedAdminUserId,
        GridWorkspaceRequest $request,
    ): GridWorkspaceMutationResult {
        $action = $this->guard->assertAllowed($request);
        $this->handler->handle(
            $authenticatedAdminUserId,
            $action,
            $request->post,
        );

        return new GridWorkspaceMutationResult(
            action: $action,
            message: GridWorkspaceMutationMessages::forAction($action),
            redirectPath: PageGridWorkspace::ACTION,
        );
    }
}
