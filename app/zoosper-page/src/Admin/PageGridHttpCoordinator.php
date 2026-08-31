<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\AdminGrid\GridWorkspaceMutationGuard;
use Zoosper\AdminGrid\GridWorkspacePostState;
use Zoosper\AdminGrid\GridWorkspaceRequest;

/**
 * Coordinates the Pages GET workspace and already-authenticated, CSRF-validated
 * POST mutations without accepting client-owned user or grid identity.
 */
final readonly class PageGridHttpCoordinator
{
    public function __construct(
        private PageGridWorkspace $workspace,
        private PageGridMutationHandler $mutations,
        private GridWorkspaceMutationGuard $guard,
    ) {
    }

    /**
     * @return array{state: \Zoosper\AdminGrid\GridViewState, html: string}
     */
    public function view(int $adminUserId, GridWorkspaceRequest $request): array
    {
        return $this->workspace->resolve(
            $adminUserId,
            PageGridQueryState::fromQuery($request->query),
            PageGridQueryState::bookmarkId($request->query),
        );
    }

    public function mutate(int $adminUserId, GridWorkspaceRequest $request): void
    {
        $action = $this->guard->assertAllowed($request);
        $post = $request->post;
        $post['filters'] = GridWorkspacePostState::fromPost($post)['filters'];

        $this->mutations->handle($adminUserId, $action, $post);
    }
}










