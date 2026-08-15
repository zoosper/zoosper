<?php

declare(strict_types=1);

namespace Zoosper\Api\Controller;

use JsonException;
use Zoosper\Auth\Token\PersonalAccessTokenAuthenticator;
use Zoosper\Auth\Token\PersonalAccessTokenPrincipal;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Page\Model\Page;
use Zoosper\Page\Repository\PageRepository;

final readonly class PageApiController
{
    public function __construct(private JsonResponder $json, private PersonalAccessTokenAuthenticator $auth, private PageRepository $pages) {}

    public function index(Request $request): Response
    {
        $principal = $this->principal($request);
        if ($principal instanceof Response) { return $principal; }
        $siteId = $request->siteContext()?->siteId;
        if ($siteId === null) { return $this->json->error('site_not_found', 'No active site exists for this host.', 404); }
        return $this->json->success(['pages' => array_map($this->normalise(...), $this->pages->allForSite($siteId))]);
    }

    public function show(Request $request): Response
    {
        $principal = $this->principal($request);
        if ($principal instanceof Response) { return $principal; }
        $page = $this->pages->findById((int) $request->routeParam('id', '0'));
        if ($page === null || $page->siteId !== $request->siteContext()?->siteId) { return $this->json->error('page_not_found', 'Page does not exist for this Site.', 404); }
        return $this->json->success(['page' => $this->normalise($page)]);
    }

    private function principal(Request $request): PersonalAccessTokenPrincipal|Response
    {
        $principal = $this->auth->authenticate($request->header('authorization'));
        if ($principal === null) { return $this->json->error('invalid_bearer_token', 'A valid bearer token is required.', 401); }
        if (!$principal->allows('pages:read') || (!$principal->user->can('page.view') && !$principal->user->can('page.manage'))) {
            return $this->json->error('insufficient_scope', 'The bearer token cannot read Pages.', 403);
        }
        return $principal;
    }

    /** @return array<string,mixed> */
    private function normalise(Page $page): array
    {
        $document = null;
        if ($page->contentJson !== null && trim($page->contentJson) !== '') {
            try { $decoded = json_decode($page->contentJson, true, 512, JSON_THROW_ON_ERROR); $document = is_array($decoded) ? $decoded : null; } catch (JsonException) { $document = null; }
        }
        return ['id'=>$page->id,'site_id'=>$page->siteId,'title'=>$page->title,'slug'=>$page->slug,'status'=>$page->status,'content_format'=>$page->contentFormat,'content_json'=>$document,'content_html'=>$page->content,'seo'=>['meta_title'=>$page->metaTitle,'meta_description'=>$page->metaDescription,'meta_keywords'=>$page->metaKeywords,'canonical_url'=>$page->canonicalUrl],'published_at'=>$page->publishedAt,'created_at'=>$page->createdAt,'updated_at'=>$page->updatedAt];
    }
}
