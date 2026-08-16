<?php

declare(strict_types=1);

namespace Zoosper\Api\Controller;

use Zoosper\Auth\Token\PersonalAccessTokenAuthenticator;
use Zoosper\Auth\Token\PersonalAccessTokenPrincipal;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Menu\Contract\MenuAdminRepositoryInterface;
use Zoosper\Menu\Contract\MenuProviderInterface;
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
