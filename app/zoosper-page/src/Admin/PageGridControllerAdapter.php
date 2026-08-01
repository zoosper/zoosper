<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\AdminGrid\GridWorkspaceCsrf;
use Zoosper\AdminGrid\GridWorkspaceRequest;

/**
 * Thin, framework-neutral adapter used after the host controller has completed
 * authentication, Page permission checks and CSRF validation where required.
 */
final readonly class PageGridControllerAdapter
{
    public function __construct(
        private PageGridPageBuilder $pages,
        private PageGridMutationCoordinator $mutations,
        private PageGridExportRequestCoordinator $exports,
    ) {
    }

    public function index(
        int $authenticatedAdminUserId,
        GridWorkspaceRequest $request,
        GridWorkspaceCsrf $csrf,
    ): PageGridResponse {
        return PageGridResponse::html(
            $this->pages->build($authenticatedAdminUserId, $request, $csrf)->html(),
        );
    }

    public function mutate(
        int $authenticatedAdminUserId,
        GridWorkspaceRequest $request,
    ): PageGridResponse {
        $result = $this->mutations->mutate($authenticatedAdminUserId, $request);

        return PageGridResponse::redirect($result->redirectPath);
    }

    public function export(
        int $authenticatedAdminUserId,
        GridWorkspaceRequest $request,
    ): PageGridResponse {
        $result = $this->exports->export($authenticatedAdminUserId, $request);

        return new PageGridResponse(200, $result->headers(), $result->csv);
    }
}
