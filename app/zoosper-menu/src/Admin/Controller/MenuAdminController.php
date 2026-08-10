<?php
declare(strict_types=1);
namespace Zoosper\Menu\Admin\Controller;
use RuntimeException; use Throwable; use Zoosper\Auth\Model\AdminUser; use Zoosper\Auth\Service\SessionGuard; use Zoosper\Core\Http\{Request,Response}; use Zoosper\Core\Url\AdminUrlGenerator; use Zoosper\Menu\Admin\MenuAdminResponder; use Zoosper\Menu\Application\MenuAdminService; use Zoosper\Menu\Contract\MenuAdminRepositoryInterface;
/** Thin HTTP adapter. Authentication, CSRF and permission checks are middleware-owned. */
final readonly class MenuAdminController {
 public function __construct(private SessionGuard $guard,private MenuAdminResponder $views,private MenuAdminService $service,private MenuAdminRepositoryInterface $menus,private AdminUrlGenerator $urls){}
 public function index(Request $r): Response{return $this->views->index($this->user());} public function create(Request $r): Response{return $this->views->create($this->user());} public function edit(Request $r): Response{return $this->views->edit($this->user(),$this->id($r,'id'));}
 public function store(Request $r): Response{try{$this->user();$id=$this->service->saveMenu($r->form());return Response::redirect($this->urls->to("menus/{$id}/edit"));}catch(Throwable $e){return Response::html($e->getMessage(),422);}}
 public function update(Request $r): Response{$this->user();$id=$this->id($r,'id');$this->service->saveMenu($r->form(),$id);return Response::redirect($this->urls->to("menus/{$id}/edit"));}
 public function addItem(Request $r): Response{$this->user();$id=$this->id($r,'id');$this->service->saveItem($id,$r->form());return Response::redirect($this->urls->to("menus/{$id}/edit"));}
 public function updateItem(Request $r): Response{$this->user();$menuId=$this->id($r,'id');$this->service->saveItem($menuId,$r->form(),$this->id($r,'itemId'));return Response::redirect($this->urls->to("menus/{$menuId}/edit"));}
 public function deleteItem(Request $r): Response{$this->user();$menuId=$this->id($r,'id');$this->menus->deleteItem($this->id($r,'itemId'));return Response::redirect($this->urls->to("menus/{$menuId}/edit"));}
 public function delete(Request $r): Response{$this->user();$this->menus->deleteMenu($this->id($r,'id'));return Response::redirect($this->urls->to('menus'));}
 private function id(Request $r,string $name): int{$v=$r->routeParam($name);if($v===null||!ctype_digit($v))throw new RuntimeException("Valid {$name} is required.");return(int)$v;} private function user(): AdminUser{return $this->guard->user()??throw new RuntimeException('Authenticated admin user required after middleware guard.');}
}
