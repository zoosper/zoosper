<?php
declare(strict_types=1);
namespace Zoosper\Theme\Api;
use RuntimeException;use Zoosper\Auth\Token\BearerPrincipalContext;use Zoosper\Core\Http\{JsonResponder,Request,Response};use Zoosper\Theme\Application\ThemeAssignmentService;use Zoosper\Theme\Theme\ThemeRepository;
/**
 * Declarative runtime entry point discovered through module configuration.
 *
 * @psalm-api
 */
final readonly class ThemeApiController{public function __construct(private JsonResponder $json,private BearerPrincipalContext $principals,private ThemeRepository $themes,private ThemeAssignmentService $assignment){}public function index(Request $r):Response{$this->principals->require($r);return$this->json->success(['themes'=>array_map(static fn(array $t):array=>['code'=>$t['code'],'name'=>$t['name'],'version'=>$t['version']],$this->themes->all())]);}public function assign(Request $r):Response{$this->principals->require($r);$code=trim((string)($r->json()['theme_code']??''));try{$this->assignment->assign((int)$r->routeParam('id','0'),$code);}catch(RuntimeException $e){return$this->json->error('theme_assignment_failed',$e->getMessage(),422);}return$this->json->success(['site_id'=>(int)$r->routeParam('id','0'),'theme_code'=>$code]);}}
