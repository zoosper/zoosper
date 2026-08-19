<?php
declare(strict_types=1);
namespace Zoosper\Admin\Controller;
use RuntimeException;
use Zoosper\Admin\Audit\LoginHistoryRepository;
use Zoosper\Admin\Audit\Grid\LoginHistoryGridDefinition;
use Zoosper\Admin\Audit\Grid\OperationalGridPageBuilder;
use Zoosper\Admin\Audit\Grid\OperationalGridQueryState;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Url\AdminUrlGenerator;
final readonly class LoginHistoryController
{
 public function __construct(private SessionGuard $guard,private LoginHistoryRepository $source,private LoginHistoryGridDefinition $definition,private OperationalGridPageBuilder $pages,private AdminLayout $layout,private ?AdminViewRenderer $views=null,private ?AdminUrlGenerator $adminUrls=null){}
 public function index(Request $request):Response
 {
  $user=$this->currentAdminUser();
  $definition=$this->definition->build(); $action=$this->adminUrls?->url('login-history')??'/admin/login-history';
  $page=$this->pages->build('Login History',$user->id,LoginHistoryGridDefinition::KEY,$action,$definition,$this->source,OperationalGridQueryState::fromRequest($request,$definition),OperationalGridQueryState::bookmarkId($request));
  $html=$page->workspaceHtml.$page->gridHtml;
  if($this->views!==null)return Response::html($this->views->render(title:'Login History',template:'zoosper-admin::login-history/index',data:['workspaceHtml'=>$page->workspaceHtml,'gridHtml'=>$page->gridHtml],user:$user,active:'login-history'));
  return Response::html($this->layout->render('Login History',$html,$user,'login-history'));
 }
 private function currentAdminUser()
 {
  $user=$this->guard->user();
  if($user===null)throw new RuntimeException('Authenticated admin user required after middleware guard.');
  return $user;
 }

}
