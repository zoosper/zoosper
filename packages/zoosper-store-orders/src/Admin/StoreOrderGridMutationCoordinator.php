<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Admin;

use Zoosper\AdminGrid\GridWorkspaceMutationGuard;
use Zoosper\AdminGrid\GridWorkspaceMutationMessages;
use Zoosper\AdminGrid\GridWorkspaceMutationResult;
use Zoosper\AdminGrid\GridWorkspaceRequest;

final readonly class StoreOrderGridMutationCoordinator
{
    public function __construct(
        private StoreOrderGridMutationHandler $handler,
        private GridWorkspaceMutationGuard $guard,
    ) {}

    public function mutate(int $adminUserId, GridWorkspaceRequest $request): GridWorkspaceMutationResult
    {
        $action = $this->guard->assertAllowed($request);
        $this->handler->handle($adminUserId, $action, $request->post);
        return new GridWorkspaceMutationResult(
            $action,
            GridWorkspaceMutationMessages::forAction($action),
            StoreOrderGridWorkspace::ACTION,
        );
    }
}
