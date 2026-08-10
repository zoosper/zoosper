<?php
declare(strict_types=1);
namespace Zoosper\Menu\Api;
use Zoosper\Core\Http\{Request,Response}; use Zoosper\Menu\Contract\MenuProviderInterface;
final readonly class MenuController { public function __construct(private MenuProviderInterface $menus){} public function show(Request $request): Response{$site=$request->siteContext();if($site===null||$site->siteId===null)return Response::json(['error'=>'Site context is required.'],400);$code=trim((string)$request->query('code','main'));$path=(string)$request->query('path','/');$nodes=$this->menus->tree($site->siteId,$code,$path);return Response::json(['data'=>['site_id'=>$site->siteId,'code'=>$code,'items'=>array_map(static fn($n)=>$n->toArray(),$nodes)]]);} }
