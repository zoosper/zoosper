<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\Controller;

use Zoosper\Core\Http\Response;
use Zoosper\Page\Admin\PageMomentumAdminResponseFactory;

/**
 * HTTP-facing controller for the live /admin/page-momentum route.
 *
 * The older PageMomentumAdminController stays as the string renderer used by
 * tests and internal composition. This controller adapts it to the Response
 * object required by ModuleRouteLoader's live handler pipeline.
 */
final class PageMomentumAdminHttpController
{
    public function __construct(
        private readonly PageMomentumAdminController $renderer = new PageMomentumAdminController(),
        private readonly PageMomentumAdminResponseFactory $responses = new PageMomentumAdminResponseFactory(),
    ) {
    }

    public function index(): Response
    {
        return $this->responses->html($this->renderer->index());
    }
}
