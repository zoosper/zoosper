<?php
declare(strict_types=1);
namespace Zoosper\Menu\Admin\Controller;
use RuntimeException; use Throwable; use Zoosper\Auth\Model\AdminUser; use Zoosper\Auth\Service\SessionGuard; use Zoosper\Core\Http\{Request,Response}; use Zoosper\Menu\Admin\MenuAdminResponder; use Zoosper\Menu\Application\MenuAdminService; use Zoosper\Menu\Contract\MenuAdminRepositoryInterface;
final readonly class MenuAdminController {
 public function __construct(private SessionGuard $guard,private MenuAdminResponder $views,private MenuAdminService $service,private MenuAdminRepositoryInterface $menus){}
 public function index(Request $r): Response{return $this->views->index($this->user());} public function create(Request $r): Response{return $this->views->create($this->user());}
 public function edit(Request $r): Response{$this->user();return $this->views->edit($this->user(),$this->routeId($r));}
 public function store(Request $r): Response{$this->user();try{$id=$this->service->saveMenu($r->form());return Response::redirect('/admin/menus/'.$id.'/edit');}catch(Throwable $e){return Response::html($e->getMessage(),422);}}
 public function update(Request $r): Response{$this->user();$id=$this->routeId($r);$this->service->saveMenu($r->form(),$id);return Response::redirect('/admin/menus/'.$id.'/edit');}
 public function addItem(Request $r): Response{$this->user();$id=$this->routeId($r);$this->service->saveItem($id,$r->form());return Response::redirect('/admin/menus/'.$id.'/edit');}
 public function delete(Request $r): Response{$this->user();$this->menus->deleteMenu($this->routeId($r));return Response::redirect('/admin/menus');}
 private function routeId(Request $r): int{$v=$r->routeParam('id');if($v===null||!ctype_digit($v))throw new RuntimeException('Valid menu ID required.');return(int)$v;} private function user(): AdminUser{return $this->guard->user()??throw new RuntimeException('Authenticated admin user required after middleware guard.');}
}
