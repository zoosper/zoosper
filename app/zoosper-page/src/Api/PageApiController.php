<?php

declare(strict_types=1);

namespace Zoosper\Page\Api;

use JsonException;
use Zoosper\Auth\Token\PersonalAccessTokenAuthenticator;
use Zoosper\Auth\Token\PersonalAccessTokenPrincipal;
use Zoosper\Audit\Contract\AuditLoggerInterface;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Page\Application\Save\PageSaveCoordinator;
use Zoosper\Page\Application\Publication\PagePublicationCoordinator;
use Zoosper\Page\Model\PageRevision;
use Zoosper\Page\Service\PageRevisionService;
use Zoosper\Page\Content\BlockJsonToHtmlRenderer;
use Zoosper\Page\Model\Page;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Lifecycle\PageLifecycleCoordinator;
use Zoosper\Page\Api\PageLifecycleApiResponder;

final readonly class PageApiController
{
    public function __construct(private JsonResponder $json, private PersonalAccessTokenAuthenticator $auth, private PageRepository $pages, private PageSaveCoordinator $saver, private BlockJsonToHtmlRenderer $renderer, private PagePublicationCoordinator $publication, private PageRevisionService $revisions, private PageLifecycleCoordinator $lifecycle, private PageLifecycleApiResponder $lifecycleResponder, private ?AuditLoggerInterface $audit = null) {}

    public function index(Request $request): Response { $p=$this->principal($request,'pages:read',true);if($p instanceof Response)return $p;$site=$request->siteContext()?->siteId;if($site===null)return $this->json->error('site_not_found','No active site exists for this host.',404);return $this->json->success(['pages'=>array_map($this->normalise(...),$this->pages->allForSite($site))]); }
    public function show(Request $request): Response { $p=$this->principal($request,'pages:read',true);if($p instanceof Response)return $p;$page=$this->sitePage($request);return $page===null?$this->json->error('page_not_found','Page does not exist for this Site.',404):$this->json->success(['page'=>$this->normalise($page)]); }

    public function create(Request $request): Response
    {
        $p=$this->principal($request,'pages:write');if($p instanceof Response)return $p;$site=$request->siteContext()?->siteId;if($site===null)return $this->json->error('site_not_found','No active site exists for this host.',404);
        $form=$this->mutationForm($request->json(),$site,null);if($form instanceof Response)return $form;
        $result=$this->saver->create($form,$p->user);if(!$result->successful)return $this->json->error('page_validation_failed',$result->error??'Page validation failed.',422);
        $page=$this->pages->findById((int)$result->pageId);if($page===null)return $this->json->error('page_not_found','Created Page could not be reloaded.',500);
        $this->audit($p,'page.api_created',$page,array_keys($request->json()));return $this->json->success(['page'=>$this->normalise($page)],201);
    }

    public function update(Request $request): Response
    {
        $p=$this->principal($request,'pages:write');if($p instanceof Response)return $p;$page=$this->sitePage($request);if($page===null)return $this->json->error('page_not_found','Page does not exist for this Site.',404);
        $form=$this->mutationForm($request->json(),$page->siteId,$page);if($form instanceof Response)return $form;
        $result=$this->saver->update($form,$page,$p->user);if(!$result->successful)return $this->json->error('page_validation_failed',$result->error??'Page validation failed.',422);
        $updated=$this->pages->findById($page->id);if($updated===null)return $this->json->error('page_not_found','Updated Page could not be reloaded.',500);
        $this->audit($p,'page.api_updated',$updated,array_keys($request->json()));return $this->json->success(['page'=>$this->normalise($updated)]);
    }

    public function archive(Request $request): Response
    {
        $principal = $this->principal($request, 'pages:archive');
        if ($principal instanceof Response) return $principal;
        $page = $this->sitePage($request);
        if ($page === null) return $this->json->error('page_not_found', 'Page does not exist for this Site.', 404);
        $result = $this->lifecycle->archive($page, $principal->user->id, $principal->user->email);
        return $this->lifecycleResponder->respond($result, $this->pages->findById($page->id));
    }
    public function restoreArchived(Request $request): Response
    {
        $principal = $this->principal($request, 'pages:archive');
        if ($principal instanceof Response) return $principal;
        $page = $this->sitePage($request);
        if ($page === null) return $this->json->error('page_not_found', 'Page does not exist for this Site.', 404);
        $result = $this->lifecycle->restore($page, $principal->user->id, $principal->user->email);
        return $this->lifecycleResponder->respond($result, $this->pages->findById($page->id));
    }
    public function deletePermanently(Request $request): Response
    {
        $principal = $this->principal($request, 'pages:delete');
        if ($principal instanceof Response) return $principal;
        $page = $this->sitePage($request);
        if ($page === null) return $this->json->error('page_not_found', 'Page does not exist for this Site.', 404);
        $result = $this->lifecycle->deletePermanently($page, $principal->user->id, $principal->user->email);
        return $this->lifecycleResponder->respond($result, null);
    }
    public function publish(Request $request): Response
    {
        return $this->publicationMutation($request, true);
    }

    public function unpublish(Request $request): Response
    {
        return $this->publicationMutation($request, false);
    }

    public function revision(Request $request): Response
    {
        $principal = $this->principal($request, 'pages:read', true);
        if ($principal instanceof Response) {
            return $principal;
        }
        $page = $this->sitePage($request);
        if ($page === null) {
            return $this->json->error('page_not_found', 'Page does not exist for this Site.', 404);
        }
        $revisionId = (int) $request->routeParam('revisionId', '0');
        try {
            $revision = $this->revisions->revision($page->id, $revisionId);
        } catch (\RuntimeException) {
            return $this->json->error('revision_not_found', 'Revision does not exist for this Page and Site.', 404);
        }
        return $this->json->success(['revision' => $this->normaliseRevision($revision)]);
    }
    public function revisions(Request $request): Response
    {
        $principal = $this->principal($request, 'pages:read', true);
        if ($principal instanceof Response) return $principal;
        $page = $this->sitePage($request);
        if ($page === null) return $this->json->error('page_not_found', 'Page does not exist for this Site.', 404);
        $requested = filter_var($request->query('page', '1'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
        $requestedPageSize = filter_var(
            $request->query('page_size', '20'),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 100]],
        );
        $pageSize = $requestedPageSize === false ? 20 : $requestedPageSize;
        $total = $this->revisions->historyCount($page->id);
        $pageCount = max(1, (int) ceil($total / $pageSize));
        $current = min($requested, $pageCount);
        return $this->json->success(['revisions' => array_map($this->normaliseRevision(...), $this->revisions->historyPage($page->id, $current, $pageSize)), 'pagination' => ['page' => $current, 'page_size' => $pageSize, 'page_count' => $pageCount, 'total' => $total]]);
    }

    public function restoreRevision(Request $request): Response
    {
        $principal = $this->principal($request, 'pages:write');
        if ($principal instanceof Response) return $principal;
        $page = $this->sitePage($request);
        if ($page === null) return $this->json->error('page_not_found', 'Page does not exist for this Site.', 404);
        $revisionId = (int) $request->routeParam('revisionId', '0');
        try {
            $revision = $this->revisions->restore($page, $revisionId, $principal->user->id, $this->pages);
        } catch (\RuntimeException) {
            return $this->json->error('revision_not_found', 'Revision does not exist for this Page and Site.', 404);
        }
        $restored = $this->pages->findById($page->id);
        if ($restored === null || $restored->siteId !== $request->siteContext()?->siteId) return $this->json->error('page_not_found', 'Page does not exist for this Site.', 404);
        $this->audit($principal, 'page.api_revision_restored', $restored, ['revision_id']);
        return $this->json->success(['page' => $this->normalise($restored), 'restored_revision' => $this->normaliseRevision($revision)]);
    }

    private function publicationMutation(Request $request, bool $publish): Response
    {
        $principal = $this->principal($request, 'pages:publish');
        if ($principal instanceof Response) return $principal;
        $page = $this->sitePage($request);
        if ($page === null) return $this->json->error('page_not_found', 'Page does not exist for this Site.', 404);
        if ($page->status === 'archived') return $this->json->error('page_state_conflict', 'Archived Pages cannot be published or unpublished.', 409);
        $target = $publish ? 'published' : 'draft';
        if ($page->status !== $target) {
            $this->revisions->capturePage($page, $principal->user->id);
            $publish ? $this->publication->publish($page, $principal->user->id) : $this->publication->unpublish($page, $principal->user->id);
        }
        $updated = $this->pages->findById($page->id);
        if ($updated === null) return $this->json->error('page_not_found', 'Page does not exist for this Site.', 404);
        $this->audit($principal, $publish ? 'page.api_published' : 'page.api_unpublished', $updated, ['status']);
        return $this->json->success(['page' => $this->normalise($updated)]);
    }

    /** @return array<string,mixed> */
    private function normaliseRevision(PageRevision $revision): array
    {
        $document = null;
        if ($revision->contentJson !== null && trim($revision->contentJson) !== '') {
            try { $decoded = json_decode($revision->contentJson, true, 512, JSON_THROW_ON_ERROR); $document = is_array($decoded) ? $decoded : null; } catch (JsonException) {}
        }
        return ['id' => $revision->id, 'page_id' => $revision->pageId, 'title' => $revision->title, 'slug' => $revision->slug, 'status' => $revision->status, 'content_format' => $revision->contentFormat, 'content_json' => $document, 'content_html' => $revision->content, 'seo' => ['meta_title' => $revision->metaTitle, 'meta_description' => $revision->metaDescription, 'meta_keywords' => $revision->metaKeywords, 'canonical_url' => $revision->canonicalUrl], 'created_by' => $revision->createdBy, 'created_at' => $revision->createdAt];
    }

    private function principal(Request $request,string $scope,bool $read=false): PersonalAccessTokenPrincipal|Response
    { $p=$this->auth->authenticate($request->header('authorization'));if($p===null)return $this->json->error('invalid_bearer_token','A valid bearer token is required.',401);$allowed=$read?($p->user->can('page.view')||$p->user->can('page.manage')):$p->user->can('page.manage');if(!$p->allows($scope)||!$allowed)return $this->json->error('insufficient_scope','The bearer token cannot perform this Page operation.',403);return $p; }
    private function sitePage(Request $request): ?Page { $page=$this->pages->findById((int)$request->routeParam('id','0'));return $page!==null&&$page->siteId===$request->siteContext()?->siteId?$page:null; }

    /** @param array<string,mixed> $body @return array<string,mixed>|Response */
    private function mutationForm(array $body,int $siteId,?Page $page): array|Response
    {
        $seo=is_array($body['seo']??null)?$body['seo']:[];$format=(string)($body['content_format']??$page?->contentFormat??'html');$json=$body['content_json']??($page?->contentJson!==null?json_decode($page->contentJson,true):null);$html=(string)($body['content_html']??$page?->content??'');
        if($format==='block_json'){
            if(!is_array($json))return $this->json->error('invalid_content_document','content_json must be an object containing blocks.',422);
            $html=$this->renderer->render($json);
        }
        if(!in_array($format,['html','block_json'],true))return $this->json->error('page_validation_failed','Unsupported content_format.',422);
        return ['site_id'=>$siteId,'title'=>$body['title']??$page?->title??'','slug'=>$body['slug']??$page?->slug??'','content'=>$html,'content_format'=>$format,'content_json'=>$json===null?'':json_encode($json,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),'meta_title'=>$seo['meta_title']??$page?->metaTitle,'meta_description'=>$seo['meta_description']??$page?->metaDescription,'meta_keywords'=>$seo['meta_keywords']??$page?->metaKeywords,'canonical_url'=>$seo['canonical_url']??$page?->canonicalUrl];
    }
    /** @param list<string> $changed */
    private function audit(PersonalAccessTokenPrincipal $p,string $action,Page $page,array $changed): void { $this->audit?->logAction($p->user->id,$p->user->email,$action,'page',(string)$page->id,$action,['page_id'=>$page->id,'site_id'=>$page->siteId,'token_id'=>$p->token->id,'token_public_id'=>$p->token->publicId,'changed_fields'=>$changed,'status'=>$page->status]); }
    /** @return array<string,mixed> */
    private function normalise(Page $page): array { $document=null;if($page->contentJson!==null&&trim($page->contentJson)!==''){try{$decoded=json_decode($page->contentJson,true,512,JSON_THROW_ON_ERROR);$document=is_array($decoded)?$decoded:null;}catch(JsonException){$document=null;}}return ['id'=>$page->id,'site_id'=>$page->siteId,'title'=>$page->title,'slug'=>$page->slug,'status'=>$page->status,'content_format'=>$page->contentFormat,'content_json'=>$document,'content_html'=>$page->content,'seo'=>['meta_title'=>$page->metaTitle,'meta_description'=>$page->metaDescription,'meta_keywords'=>$page->metaKeywords,'canonical_url'=>$page->canonicalUrl],'published_at'=>$page->publishedAt,'created_at'=>$page->createdAt,'updated_at'=>$page->updatedAt]; }
}










