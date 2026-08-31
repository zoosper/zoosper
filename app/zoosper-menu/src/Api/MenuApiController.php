<?php

declare(strict_types=1);

namespace Zoosper\Menu\Api;

use Zoosper\Auth\Token\PersonalAccessTokenAuthenticator;
use Zoosper\Auth\Token\PersonalAccessTokenPrincipal;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Menu\Contract\MenuAdminRepositoryInterface;
use Zoosper\Menu\Contract\MenuProviderInterface;
use Zoosper\Menu\Application\{MenuAdminService,MenuItemDeletionService,MenuMutationGuard};
use Zoosper\Menu\Lifecycle\{MenuLifecycleCoordinator,MenuLifecycleResult};
use Zoosper\Audit\Contract\AuditLoggerInterface;
use InvalidArgumentException;
use RuntimeException;
use Zoosper\Menu\Model\Menu;
use Zoosper\Menu\Model\MenuItem;

/** Stateless PAT adapter for request-Site Menu reads. */
final readonly class MenuApiController
{
    public function __construct(
        private JsonResponder $json,
        private PersonalAccessTokenAuthenticator $auth,
        private MenuAdminRepositoryInterface $menus,
        private MenuProviderInterface $provider,
        private MenuAdminService $mutations,
        private MenuMutationGuard $guard,
        private MenuItemDeletionService $deletion,
        private MenuLifecycleCoordinator $lifecycle,
        private ?AuditLoggerInterface $audit = null,
    ) {}

    public function index(Request $request): Response
    {
        $principal=$this->principal($request); if($principal instanceof Response)return $principal;
        $siteId=$request->siteContext()?->siteId; if($siteId===null)return $this->json->error('site_not_found','No active site exists for this host.',404);
        $menus=array_values(array_filter($this->menus->all(),static fn(Menu $menu):bool=>$menu->siteId===$siteId));
        return $this->json->success(['menus'=>array_map($this->normaliseMenu(...),$menus)]);
    }

    public function show(Request $request): Response
    {
        $principal=$this->principal($request); if($principal instanceof Response)return $principal;
        $menu=$this->siteMenu($request); if($menu===null)return $this->json->error('menu_not_found','Menu does not exist for this Site.',404);
        return $this->json->success(['menu'=>$this->normaliseMenu($menu),'items'=>array_map($this->normaliseItem(...),$this->menus->items($menu->id))]);
    }

    public function tree(Request $request): Response
    {
        $principal=$this->principal($request); if($principal instanceof Response)return $principal;
        $menu=$this->siteMenu($request); if($menu===null)return $this->json->error('menu_not_found','Menu does not exist for this Site.',404);
        if($menu->status!=='active')return $this->json->error('menu_not_active','Only active Menus have a resolved frontend tree.',409);
        $path=(string)$request->query('current_path','/');
        return $this->json->success(['menu'=>$this->normaliseMenu($menu),'tree'=>$this->provider->tree($menu->siteId,$menu->code,$path)]);
    }

    public function create(Request $request): Response
    {
        $principal=$this->writePrincipal($request);if($principal instanceof Response)return $principal;$siteId=$request->siteContext()?->siteId;if($siteId===null)return $this->json->error('site_not_found','No active site exists for this host.',404);
        $input=$request->json();$input['site_id']=$siteId;
        try{$id=$this->mutations->saveMenu($input);}catch(InvalidArgumentException $e){return $this->json->error('menu_validation_failed',$e->getMessage(),422);}
        $menu=$this->guard->siteMenu($id,$siteId);$this->audit($principal,'menu.api_created',$menu,['code','label','status']);return $this->json->success(['menu'=>$this->normaliseMenu($menu)],201);
    }
    public function update(Request $request): Response
    {
        $principal=$this->writePrincipal($request);if($principal instanceof Response)return $principal;$menu=$this->siteMenu($request);if($menu===null)return $this->json->error('menu_not_found','Menu does not exist for this Site.',404);
        $input=$request->json();$input['site_id']=$menu->siteId;$input['code']=$input['code']??$menu->code;$input['label']=$input['label']??$menu->label;$input['status']=$input['status']??$menu->status;
        try{$this->mutations->saveMenu($input,$menu->id);}catch(InvalidArgumentException $e){return $this->json->error('menu_validation_failed',$e->getMessage(),422);}
        $updated=$this->menus->find($menu->id);$this->audit($principal,'menu.api_updated',$updated,array_keys($request->json()));return $this->json->success(['menu'=>$this->normaliseMenu($updated)]);
    }
    public function createItem(Request $request): Response{return $this->saveItem($request,null);}
    public function updateItem(Request $request): Response{return $this->saveItem($request,(int)$request->routeParam('itemId','0'));}
    public function deleteItem(Request $request): Response
    {
        $principal=$this->writePrincipal($request);if($principal instanceof Response)return $principal;$menu=$this->siteMenu($request);if($menu===null)return $this->json->error('menu_not_found','Menu does not exist for this Site.',404);$itemId=(int)$request->routeParam('itemId','0');
        try{$this->deletion->delete($menu->id,$itemId);}catch(RuntimeException $e){return $this->json->error('menu_item_delete_blocked',$e->getMessage(),409);}
        $this->audit($principal,'menu.api_item_deleted',$menu,['item_id']);return $this->json->success(['deleted'=>true,'item_id'=>$itemId]);
    }
    public function disable(Request $request): Response{return $this->lifecycle($request,'disable');}
    public function restore(Request $request): Response{return $this->lifecycle($request,'restore');}
    public function deletePermanently(Request $request): Response{return $this->lifecycle($request,'delete');}
    private function saveItem(Request $request,?int $itemId): Response
    {
        $principal=$this->writePrincipal($request);if($principal instanceof Response)return $principal;$menu=$this->siteMenu($request);if($menu===null)return $this->json->error('menu_not_found','Menu does not exist for this Site.',404);
        try{$input=$this->guard->itemInput($menu->id,$menu->siteId,$request->json(),$itemId);$id=$this->mutations->saveItem($menu->id,$input,$itemId);}catch(InvalidArgumentException $e){return $this->json->error('menu_item_validation_failed',$e->getMessage(),422);}catch(RuntimeException $e){return $this->json->error('menu_item_conflict',$e->getMessage(),409);}
        $item=null;foreach($this->menus->items($menu->id) as $candidate){if($candidate->id===$id){$item=$candidate;break;}}if($item===null)return $this->json->error('menu_item_not_found','Menu item could not be reloaded.',404);
        $this->audit($principal,$itemId===null?'menu.api_item_created':'menu.api_item_updated',$menu,array_keys($request->json()));return $this->json->success(['item'=>$this->normaliseItem($item)],$itemId===null?201:200);
    }
    private function lifecycle(Request $request,string $operation): Response
    {
        $principal=$this->writePrincipal($request);if($principal instanceof Response)return $principal;$menu=$this->siteMenu($request);if($menu===null)return $this->json->error('menu_not_found','Menu does not exist for this Site.',404);
        $result=match($operation){'disable'=>$this->lifecycle->disable($menu,$principal->user->id,$principal->user->email),'restore'=>$this->lifecycle->restore($menu,$principal->user->id,$principal->user->email),'delete'=>$this->lifecycle->deletePermanently($menu,$principal->user->id,$principal->user->email),default=>throw new RuntimeException('Unsupported Menu lifecycle operation.')};
        if(!$result->successful)return $this->json->error('menu_lifecycle_blocked',$result->message??'Menu lifecycle operation was blocked.',409,['blockers'=>$result->blockers]);
        if($operation==='delete')return $this->json->success(['deleted'=>true,'menu_id'=>$menu->id]);$updated=$this->menus->find($menu->id);return $this->json->success(['menu'=>$this->normaliseMenu($updated)]);
    }
    private function writePrincipal(Request $request): PersonalAccessTokenPrincipal|Response
    {
        $principal=$this->auth->authenticate($request->header('authorization'));if($principal===null)return $this->json->error('invalid_bearer_token','A valid bearer token is required.',401);if(!$principal->allows('menus:write')||!$principal->user->can('menu.manage'))return $this->json->error('insufficient_scope','The bearer token cannot perform this Menu mutation.',403);return $principal;
    }
    /** @param list<string> $changed */
    private function audit(PersonalAccessTokenPrincipal $principal,string $action,Menu $menu,array $changed):void{$this->audit?->logAction($principal->user->id,$principal->user->email,$action,'menu',(string)$menu->id,$action,['menu_id'=>$menu->id,'site_id'=>$menu->siteId,'token_id'=>$principal->token->id,'token_public_id'=>$principal->token->publicId,'changed_fields'=>$changed,'status'=>$menu->status]);}

    private function principal(Request $request): PersonalAccessTokenPrincipal|Response
    {
        $principal=$this->auth->authenticate($request->header('authorization'));
        if($principal===null)return $this->json->error('invalid_bearer_token','A valid bearer token is required.',401);
        if(!$principal->allows('menus:read')||!$principal->user->can('menu.manage'))return $this->json->error('insufficient_scope','The bearer token cannot perform this Menu operation.',403);
        return $principal;
    }

    private function siteMenu(Request $request): ?Menu
    {
        $menu=$this->menus->find((int)$request->routeParam('id','0'));
        return $menu!==null&&$menu->siteId===$request->siteContext()?->siteId?$menu:null;
    }

    /** @return array<string,mixed> */
    private function normaliseMenu(Menu $menu): array{return ['id'=>$menu->id,'site_id'=>$menu->siteId,'code'=>$menu->code,'label'=>$menu->label,'status'=>$menu->status,'created_at'=>$menu->createdAt,'updated_at'=>$menu->updatedAt];}
    /** @return array<string,mixed> */
    private function normaliseItem(MenuItem $item): array{return ['id'=>$item->id,'menu_id'=>$item->menuId,'parent_id'=>$item->parentId,'page_id'=>$item->pageId,'label'=>$item->label,'url'=>$item->url,'target'=>$item->target,'position'=>$item->position,'status'=>$item->status];}
}










