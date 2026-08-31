<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\Controller;

use Throwable;
use Zoosper\AdminGrid\GridWorkspaceCsvExportService;
use Zoosper\AdminGrid\GridWorkspaceRequest;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Page\Admin\PageGridExportRequestCoordinator;

/** Thin HTTP adapter for the already-built Pages export pipeline. */
final readonly class PageCsvExportController
{
    public function __construct(
        private SessionGuard $guard,
        private PageGridExportRequestCoordinator $exports,
        private ?AdminUrlGenerator $adminUrls = null,
    ) {
    }

    public function export(Request $request): Response
    {
        $user = $this->guard->user();
        if ($user === null) {
            return Response::redirect($this->adminUrls?->url('login') ?? '/admin/login');
        }

        try {
            $result = $this->exports->export(
                $user->id,
                new GridWorkspaceRequest('GET', $request->queryParams(), []),
            );
            return Response::raw("\xEF\xBB\xBF" . $result->csv, 200, $result->headers());
        } catch (Throwable) {
            return Response::html('The Pages export is currently unavailable.', 503);
        }
    }
}










