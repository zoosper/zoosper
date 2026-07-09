<?php

declare(strict_types=1);

namespace Zoosper\Page\Controller;

use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Service\PageRenderer;
use Zoosper\Site\Service\SiteResolver;

final readonly class PageController
{
    public function __construct(
        private SiteResolver $siteResolver,
        private PageRepository $pages,
        private PageRenderer $renderer,
    ) {
    }

    public function view(Request $request): Response
    {
        $siteContext = $this->siteResolver->resolve($request->host());

        if ($siteContext === null) {
            return Response::html('<h1>Site not found</h1><p>No active site is configured for this host.</p>', 404);
        }

        $slug = trim($request->path(), '/');

        if ($slug === '') {
            $slug = $siteContext->site->homepageSlug ?: 'home';
        }

        $page = $this->pages->findPublishedBySlug($siteContext->site->id, $slug);

        if ($page === null) {
            return Response::html('<h1>Page not found</h1><p>No published page exists for this URL.</p>', 404);
        }

        return Response::html($this->renderer->render($page, $siteContext->site));
    }
}
