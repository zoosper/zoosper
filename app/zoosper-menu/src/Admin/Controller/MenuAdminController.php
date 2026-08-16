<?php
declare(strict_types=1);
namespace Zoosper\Menu\Admin\Controller;
use Zoosper\Menu\Admin\Lifecycle\MenuLifecycleAdminResponder;
use Zoosper\Menu\Application\MenuItemDeletionService;
use RuntimeException; use Throwable; use Zoosper\Auth\Model\AdminUser; use Zoosper\Auth\Service\SessionGuard; use Zoosper\Core\Http\{Request,Response}; use Zoosper\Core\Url\AdminUrlGenerator; use Zoosper\Menu\Admin\MenuAdminResponder; use Zoosper\Menu\Application\MenuAdminService; use Zoosper\Menu\Contract\MenuAdminRepositoryInterface;
/** Thin HTTP adapter. Authentication, CSRF and permission checks are middleware-owned. */
final readonly class MenuAdminController {
 public function __construct(private SessionGuard $guard,private MenuAdminResponder $views,private MenuAdminService $service,private MenuAdminRepositoryInterface $menus,private AdminUrlGenerator $urls,private ?MenuLifecycleAdminResponder $lifecycle=null,private ?MenuItemDeletionService $itemDeletion=null){}
 public function index(Request $r): Response{return $this->views->index($this->user());} public function create(Request $r): Response{return $this->views->create($this->user());} public function edit(Request $r): Response{return $this->views->edit($this->user(),$this->id($r,'id'));}
 public function store(Request $r): Response
 {
  $user=$this->user();
  try {
   $id=$this->service->saveMenu($r->form());
   return Response::redirect($this->menuAdminUrl("menus/{$id}/edit"));
  } catch (Throwable $e) {
   return $this->views->error($user,$e,'create menu',$this->menuAdminUrl('menus/create'));
  }
 }
 public function update(Request $r): Response{$this->user();$id=$this->id($r,'id');$this->service->saveMenu($r->form(),$id);return Response::redirect($this->urls->url("menus/{$id}/edit"));}
 public function addItem(Request $r): Response{$this->user();$id=$this->id($r,'id');$this->service->saveItem($id,$r->form());return Response::redirect($this->urls->url("menus/{$id}/edit"));}
 public function updateItem(Request $r): Response{$this->user();$menuId=$this->id($r,'id');$this->service->saveItem($menuId,$r->form(),$this->id($r,'itemId'));return Response::redirect($this->urls->url("menus/{$menuId}/edit"));}
 public function deleteItem(Request $r): Response{$user=$this->user();$menuId=$this->id($r,'id');$itemId=$this->id($r,'itemId');try{$this->itemDeletion?->delete($menuId,$itemId)??$this->menus->deleteItem($itemId);}catch(\Throwable $e){return $this->views->error($user,$e,'delete Menu item',$this->urls->url("menus/{$menuId}/edit"));}return Response::redirect($this->urls->url("menus/{$menuId}/edit"));}
 public function disable(Request $r): Response{return $this->lifecycleOperation($r,'disable');} public function restore(Request $r): Response{return $this->lifecycleOperation($r,'restore');} public function deletePermanently(Request $r): Response{return $this->lifecycleOperation($r,'delete');} private function lifecycleOperation(Request $r,string $operation):Response{$user=$this->user();$menu=$this->menus->find($this->id($r,'id'));if($menu===null||$this->lifecycle===null){return Response::redirect($this->urls->url('menus'),303);}return match($operation){'disable'=>$this->lifecycle->disable($menu,$user),'restore'=>$this->lifecycle->restore($menu,$user),'delete'=>$this->lifecycle->delete($menu,$user),default=>Response::redirect($this->urls->url('menus'),303)};}
 private function menuAdminUrl(string $path): string
 {
  foreach (['to','generate','url','build'] as $method) {
   if (method_exists($this->urls,$method)) {
    return $this->urls->{$method}($path);
   }
  }
  throw new RuntimeException('Admin URL generator has no supported path method.');
 }
 private function id(Request $r,string $name): int{$v=$r->routeParam($name);if($v===null||!ctype_digit($v))throw new RuntimeException("Valid {$name} is required.");return(int)$v;} private function user(): AdminUser{return $this->guard->user()??throw new RuntimeException('Authenticated admin user required after middleware guard.');}
}
