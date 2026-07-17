<?php

declare(strict_types=1);

namespace Zoosper\Page\Controller;

use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Site\SiteContext;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Service\PageRenderer;
use Zoosper\Site\Model\Site;
use Zoosper\Site\Repository\SiteRepository;

final readonly class PageController
{
    public function __construct(
        private SiteRepository $sites,
        private PageRepository $pages,
        private PageRenderer $renderer,
    ) {
    }

    public function view(Request $request): Response
    {
        $siteContext = $request->siteContext();
        if ($siteContext === null || $siteContext->siteId === null) {
            return Response::html('<h1>Site not found</h1><p>No active site is configured for this host.</p>', 404);
        }

        $site = $this->sites->findById($siteContext->siteId);
        if ($site === null || $site->status !== 'active') {
            return Response::html('<h1>Site not found</h1><p>No active site is configured for this host.</p>', 404);
        }

        $slug = $this->slugFromRequest($request, $siteContext, $site);
        $page = $this->pages->findPublishedBySlug($site->id, $slug);

        if ($page === null) {
            return Response::html('<h1>Page not found</h1><p>No published page exists for this URL.</p>', 404);
        }

        return Response::html($this->renderer->render($page, $site, $request));
    }

    private function slugFromRequest(Request $request, SiteContext $siteContext, Site $site): string
    {
        $path = '/' . ltrim(trim($request->path()), '/');
        $path = $path === '/' ? '/' : rtrim($path, '/');
        $prefix = $this->normaliseOptionalPrefix($siteContext->pathPrefix);

        if ($prefix !== '') {
            if ($path === $prefix) {
                $path = '/';
            } elseif (str_starts_with($path, rtrim($prefix, '/') . '/')) {
                $path = '/' . ltrim(substr($path, strlen(rtrim($prefix, '/'))), '/');
                $path = $path === '/' ? '/' : rtrim($path, '/');
            }
        }

        $slug = trim($path, '/');

        return $slug !== '' ? $slug : ($site->homepageSlug ?: 'home');
    }

    private function normaliseOptionalPrefix(string $prefix): string
    {
        $prefix = trim($prefix);
        if ($prefix === '') {
            return '';
        }

        $prefix = '/' . ltrim($prefix, '/');

        return $prefix === '/' ? '' : rtrim($prefix, '/');
    }
}
