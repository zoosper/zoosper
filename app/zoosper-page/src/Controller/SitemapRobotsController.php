<?php

declare(strict_types=1);

namespace Zoosper\Page\Controller;

use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Seo\SitemapGenerator;
use Zoosper\Site\Repository\SiteRepository;

final readonly class SitemapRobotsController
{
    public function __construct(private SiteRepository $sites, private PageRepository $pages, private SitemapGenerator $sitemap)
    {
    }

    public function sitemap(Request $request): Response
    {
        $site = $this->site($request);
        if ($site === null) {
            return Response::raw("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"></urlset>\n", 404, ['Content-Type' => 'application/xml; charset=utf-8', 'Cache-Control' => 'no-store']);
        }
        return Response::raw($this->sitemap->xml($site, $this->pages->allPublishedForSite($site->id)), 200, ['Content-Type' => 'application/xml; charset=utf-8', 'Cache-Control' => 'public, max-age=300']);
    }

    public function robots(Request $request): Response
    {
        $site = $this->site($request);
        $lines = ['User-agent: *', 'Allow: /'];
        $url = $site === null ? null : $this->sitemap->sitemapUrl($site);
        if ($url !== null) {
            $lines[] = 'Sitemap: ' . $url;
        }
        return Response::raw(implode("\n", $lines) . "\n", 200, ['Content-Type' => 'text/plain; charset=utf-8', 'Cache-Control' => 'public, max-age=300']);
    }

    private function site(Request $request): ?\Zoosper\Site\Model\Site
    {
        $id = $request->siteContext()?->siteId;
        $site = $id === null ? null : $this->sites->findById($id);
        return $site !== null && $site->status === 'active' ? $site : null;
    }
}
