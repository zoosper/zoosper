<?php
declare(strict_types=1);
namespace Zoosper\Audit\Controller;
use RuntimeException;
use Zoosper\Audit\AuditLogRepository;
use Zoosper\Audit\Admin\Grid\AuditLogGridDefinition;
use Zoosper\Audit\Admin\Grid\OperationalGridPageBuilder;
use Zoosper\Audit\Admin\Grid\OperationalGridQueryState;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Url\AdminUrlGenerator;
final readonly class AuditLogController
{
 public function __construct(private SessionGuard $guard,private AuditLogRepository $source,private AuditLogGridDefinition $definition,private OperationalGridPageBuilder $pages,private AdminLayout $layout,private ?AdminViewRenderer $views=null,private ?AdminUrlGenerator $adminUrls=null){}
 public function index(Request $request):Response
 {
  $user=$this->currentAdminUser();
  $definition=$this->definition->build(); $action=$this->adminUrls?->url('audit-log')??'/admin/audit-log';
  $page=$this->pages->build('Audit Log',$user->id,AuditLogGridDefinition::KEY,$action,$definition,$this->source,OperationalGridQueryState::fromRequest($request,$definition),OperationalGridQueryState::bookmarkId($request));
  $html=$page->workspaceHtml.$page->gridHtml;
  if($this->views!==null)return Response::html($this->views->render(title:'Audit Log',template:'zoosper-audit::audit-log/index',data:['workspaceHtml'=>$page->workspaceHtml,'gridHtml'=>$page->gridHtml],user:$user,active:'audit-log'));
  return Response::html($this->layout->render('Audit Log',$html,$user,'audit-log'));
 }
 private function currentAdminUser()
 {
  $user=$this->guard->user();
  if($user===null)throw new RuntimeException('Authenticated admin user required after middleware guard.');
  return $user;
 }

}











