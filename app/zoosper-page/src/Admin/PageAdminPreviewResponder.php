<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\Core\Http\Response;
use Zoosper\Page\Model\Page;
use Zoosper\Page\Service\PageRenderer;
use Zoosper\Site\Repository\SiteRepository;

/** Read-only Admin preview response using the single established PageRenderer path. */
final readonly class PageAdminPreviewResponder
{
    public function __construct(
        private SiteRepository $sites,
        private PageRenderer $renderer,
    ) {
    }

    public function respond(?Page $page): Response
    {
        if ($page === null) {
            return Response::html('<h1>Page not found</h1>', 404);
        }
        $site = $this->sites->findById($page->siteId);
        if ($site === null) {
            return Response::html('<h1>Site not found</h1>', 404);
        }

        return Response::html($this->renderer->render($page, $site));
    }
}
