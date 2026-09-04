<?php
declare(strict_types=1);
namespace Zoosper\Menu\Admin;
use Zoosper\Menu\Admin\Lifecycle\MenuLifecycleAdminResponder;
use Throwable;use Zoosper\Core\Http\Request;use Zoosper\AdminGrid\{AdminCollectionGrid,AdminCollectionGridQuery};use Zoosper\Menu\Admin\Grid\MenuGrid;
use Zoosper\Auth\Model\AdminUser; use Zoosper\Auth\Service\CsrfTokenManager; use Zoosper\Auth\UI\AdminViewRendererInterface; use Zoosper\Core\Http\Response; use Zoosper\Core\Url\AdminUrlGenerator; use Zoosper\Menu\Contract\MenuAdminRepositoryInterface;
final readonly class MenuAdminResponder {
 public function __construct(private MenuAdminRepositoryInterface $menus,private AdminViewRendererInterface $views,private CsrfTokenManager $csrf,private AdminUrlGenerator $urls,private MenuAdminChoicesProvider $choices,private ?MenuLifecycleAdminResponder $lifecycle=null,private ?MenuGrid $grid=null,private ?AdminCollectionGrid $collectionGrid=null){}
 public function index(AdminUser $user,Request $request):Response{if($this->grid===null||$this->collectionGrid===null)throw new \RuntimeException('Admin Grid services are required for Menus.');$definition=$this->grid->definition();$gridHtml=$this->collectionGrid->render($user->id,MenuGrid::KEY,$this->urls->url('menus'),$definition,$this->grid,AdminCollectionGridQuery::values($request,$definition),AdminCollectionGridQuery::bookmark($request))['html'];return Response::html($this->views->render('Menus','zoosper-menu::admin/menu/index.latte',['gridHtml'=>$gridHtml,'createUrl'=>$this->urls->url('menus/create')],$user,'menus',shellTitle:''));}
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










