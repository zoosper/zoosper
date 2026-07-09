<?php

declare(strict_types=1);

namespace Zoosper\Api\Controller;

use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Site\Service\SiteResolver;

final readonly class ContentPageController
{
    public function __construct(
        private JsonResponder $json,
        private SiteResolver $siteResolver,
        private PageRepository $pages,
    ) {
    }

    public function show(Request $request): Response
    {
        $siteContext = $this->siteResolver->resolve($request->host());

        if ($siteContext === null) {
            return $this->json->error('site_not_found', 'No active site exists for this host.', 404);
        }

        $slug = $request->query('slug', $siteContext->site->homepageSlug ?: 'home');
        $page = $this->pages->findPublishedBySlug($siteContext->site->id, (string) $slug);

        if ($page === null) {
            return $this->json->error('page_not_found', 'No published page exists for this slug.', 404);
        }

        return $this->json->success([
            'site' => [
                'id' => $siteContext->site->id,
                'code' => $siteContext->site->code,
                'name' => $siteContext->site->name,
            ],
            'page' => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'content' => $page->content,
                'status' => $page->status,
                'published_at' => $page->publishedAt,
            ],
        ]);
    }
}
