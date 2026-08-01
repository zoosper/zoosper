<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\AdminGrid\GridWorkspaceRequest;

/**
 * Framework-neutral contract for the final Page controller integration.
 *
 * Implementations must authenticate and authorise before calling either method,
 * and must validate CSRF before calling mutate().
 */
interface PageGridControllerContract
{
    /** @return array{state: \Zoosper\AdminGrid\GridViewState, html: string} */
    public function viewGrid(
        int $authenticatedAdminUserId,
        GridWorkspaceRequest $request,
    ): array;

    public function mutateGrid(
        int $authenticatedAdminUserId,
        GridWorkspaceRequest $request,
    ): \Zoosper\AdminGrid\GridWorkspaceMutationResult;
}
