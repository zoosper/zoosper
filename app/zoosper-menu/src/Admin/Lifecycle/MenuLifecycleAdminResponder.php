<?php

declare(strict_types=1);

namespace Zoosper\Menu\Admin\Lifecycle;

use Throwable;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Message\FlashMessageStoreInterface;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Menu\Lifecycle\MenuLifecycleCoordinator;
use Zoosper\Menu\Lifecycle\MenuLifecycleResult;
use Zoosper\Menu\Model\Menu;

final readonly class MenuLifecycleAdminResponder
{
    public function __construct(private MenuLifecycleCoordinator $lifecycle,private CsrfTokenManager $csrf,private AdminUrlGenerator $urls,private ?FlashMessageStoreInterface $flash=null){}
    public function actionsHtml(Menu $menu): string
    {
        $token=$this->e($this->csrf->token());$id=$menu->id;
        if($menu->status==='inactive'){return '<section class="card menu-lifecycle-actions"><h3>Menu lifecycle</h3><p class="muted">Restore this Menu, or permanently delete it only after every Menu item is removed.</p>'.$this->form("menus/{$id}/restore",$token,'Restore Menu','button secondary').$this->form("menus/{$id}/delete",$token,'Delete permanently','button button--danger',true).'</section>';}
        return '<section class="card menu-lifecycle-actions"><h3>Menu lifecycle</h3><p class="muted">Making a Menu inactive removes it from frontend and API resolution while retaining its complete item tree.</p>'.$this->form("menus/{$id}/disable",$token,'Make inactive','button secondary').'</section>';
    }
    public function disable(Menu $menu,AdminUser $actor):Response{return $this->operate('disable',$menu,$actor);} public function restore(Menu $menu,AdminUser $actor):Response{return $this->operate('restore',$menu,$actor);} public function delete(Menu $menu,AdminUser $actor):Response{return $this->operate('delete',$menu,$actor);}
    private function operate(string $operation,Menu $menu,AdminUser $actor):Response
    {
        try{$result=match($operation){'disable'=>$this->lifecycle->disable($menu,$actor->id,$actor->email),'restore'=>$this->lifecycle->restore($menu,$actor->id,$actor->email),'delete'=>$this->lifecycle->deletePermanently($menu,$actor->id,$actor->email),default=>throw new \LogicException('Unsupported Menu lifecycle operation.')};$this->present($result);}catch(Throwable $e){$this->flash?->error($e->getMessage(),'menu.lifecycle.failed');return Response::redirect($this->urls->url("menus/{$menu->id}/edit"),303);}
        return Response::redirect($operation==='delete'&&$result->successful?$this->urls->url('menus'):$this->urls->url("menus/{$menu->id}/edit"),303);
    }
    private function present(MenuLifecycleResult $result):void{if($result->successful){$this->flash?->success(match($result->operation){'disable'=>'Menu is now inactive.','restore'=>'Menu restored to active.','delete'=>'Menu permanently deleted.',default=>'Menu lifecycle operation completed.'},'menu.lifecycle.'.$result->operation);return;}$message=$result->message??'Menu lifecycle operation was blocked.';if(($result->blockers['menu_items']??0)>0){$message.=' Blocking references: '.$result->blockers['menu_items'].' Menu item(s).';}$this->flash?->error($message,'menu.lifecycle.blocked');}
    private function form(string $path,string $token,string $label,string $class,bool $destructive=false):string{$description=$destructive?'<p class="muted">This cannot be undone. Database cascades are not used as an Admin item-deletion shortcut.</p>':'';return '<form method="post" action="'.$this->e($this->urls->url($path)).'" class="menu-lifecycle-form"><input type="hidden" name="_csrf_token" value="'.$token.'">'.$description.'<button type="submit" class="'.$this->e($class).'">'.$this->e($label).'</button></form>';}
    private function e(string $value):string{return htmlspecialchars($value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
}










