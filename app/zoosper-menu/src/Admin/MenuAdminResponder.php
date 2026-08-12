<?php
declare(strict_types=1);
namespace Zoosper\Menu\Admin;
use Zoosper\Menu\Admin\Lifecycle\MenuLifecycleAdminResponder;
use Throwable;
use Zoosper\Auth\Model\AdminUser; use Zoosper\Auth\Service\CsrfTokenManager; use Zoosper\Auth\UI\AdminViewRendererInterface; use Zoosper\Core\Http\Response; use Zoosper\Core\Url\AdminUrlGenerator; use Zoosper\Menu\Contract\MenuAdminRepositoryInterface;
final readonly class MenuAdminResponder {
 public function __construct(private MenuAdminRepositoryInterface $menus,private AdminViewRendererInterface $views,private CsrfTokenManager $csrf,private AdminUrlGenerator $urls,private MenuAdminChoicesProvider $choices,private ?MenuLifecycleAdminResponder $lifecycle=null){}
 public function index(AdminUser $user): Response{return Response::html($this->views->render('Menus','zoosper-menu::admin/menu/index.latte',['menus'=>$this->menus->all(),'createUrl'=>$this->urls->url('menus/create'),'editBaseUrl'=>$this->urls->url('menus'),'token'=>$this->csrf->token()],$user,'menus'));}
 public function edit(AdminUser $user,int $id): Response{$menu=$this->menus->find($id);if($menu===null)return Response::html('Menu not found',404);return Response::html($this->views->render('Edit menu','zoosper-menu::admin/menu/edit.latte',['menu'=>$menu,'items'=>$this->menus->items($id),'sites'=>$this->choices->sites(),'pages'=>$this->choices->pages($menu->siteId),'saveUrl'=>$this->urls->url("menus/{$id}/edit"),'itemsUrl'=>$this->urls->url("menus/{$id}/items"),'deleteUrl'=>$this->urls->url("menus/{$id}/delete"),'token'=>$this->csrf->token(),'lifecycleHtml'=>$this->lifecycle?->actionsHtml($menu)??''],$user,'menus'));}
 public function create(AdminUser $user): Response{return Response::html($this->views->render('Create menu','zoosper-menu::admin/menu/edit.latte',['menu'=>null,'items'=>[],'sites'=>$this->choices->sites(),'pages'=>[],'saveUrl'=>$this->urls->url('menus'),'itemsUrl'=>'','deleteUrl'=>'','token'=>$this->csrf->token(),'lifecycleHtml'=>''],$user,'menus'));}

 public function error(AdminUser $user,Throwable $error,string $operation,string $returnUrl): Response
 {
  return Response::html($this->views->render(
   'Menu operation failed',
   'zoosper-menu::admin/menu/error.latte',
   ['heading'=>'Menu operation failed','message'=>$error->getMessage(),'exceptionClass'=>$error::class,'operation'=>$operation,'returnUrl'=>$returnUrl],
   $user,
   'menus',
  ),422);
 }
}
