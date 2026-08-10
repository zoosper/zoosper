<?php
declare(strict_types=1);
namespace Zoosper\Menu\Admin;
use Zoosper\Auth\Model\AdminUser; use Zoosper\Auth\Service\CsrfTokenManager; use Zoosper\Auth\UI\AdminViewRendererInterface; use Zoosper\Core\Http\Response; use Zoosper\Core\Url\AdminUrlGenerator; use Zoosper\Menu\Contract\MenuAdminRepositoryInterface;
final readonly class MenuAdminResponder {
 public function __construct(private MenuAdminRepositoryInterface $menus,private AdminViewRendererInterface $views,private CsrfTokenManager $csrf,private AdminUrlGenerator $urls,private MenuAdminChoicesProvider $choices){}
 public function index(AdminUser $user): Response{return Response::html($this->views->render('Menus','menu/admin/index.latte',['menus'=>$this->menus->all(),'createUrl'=>$this->urls->to('menus/create'),'editBaseUrl'=>$this->urls->to('menus'),'token'=>$this->csrf->token()],$user,'menus'));}
 public function edit(AdminUser $user,int $id): Response{$menu=$this->menus->find($id);if($menu===null)return Response::html('Menu not found',404);return Response::html($this->views->render('Edit menu','menu/admin/edit.latte',['menu'=>$menu,'items'=>$this->menus->items($id),'sites'=>$this->choices->sites(),'pages'=>$this->choices->pages($menu->siteId),'saveUrl'=>$this->urls->to("menus/{$id}/edit"),'itemsUrl'=>$this->urls->to("menus/{$id}/items"),'deleteUrl'=>$this->urls->to("menus/{$id}/delete"),'token'=>$this->csrf->token()],$user,'menus'));}
 public function create(AdminUser $user): Response{return Response::html($this->views->render('Create menu','menu/admin/edit.latte',['menu'=>null,'items'=>[],'sites'=>$this->choices->sites(),'pages'=>[],'saveUrl'=>$this->urls->to('menus'),'itemsUrl'=>'','deleteUrl'=>'','token'=>$this->csrf->token()],$user,'menus'));}
}
