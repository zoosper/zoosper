<?php

declare(strict_types=1);

it('exposes stateless PAT Menu list detail and tree reads with Site isolation',function():void{
 $root=dirname(__DIR__,5);$routes=(string)file_get_contents($root.'/app/zoosper-menu/config/api_routes.php');$controller=(string)file_get_contents($root.'/app/zoosper-menu/src/Api/MenuApiController.php');$wiring=(string)file_get_contents($root.'/app/zoosper-menu/config/controllers.php');
 foreach(['/api/v1/menus','/api/v1/menus/{id:\d+}','/api/v1/menus/{id:\d+}/tree'] as $path)expect($routes)->toContain($path);
 expect($controller)->toContain("allows('menus:read')")->toContain("can('menu.manage')")->toContain('$request->siteContext()?->siteId')->toContain('MenuProviderInterface')->not->toContain('SessionGuard')
  ->and($wiring)->toContain('MenuAdminRepositoryInterface::class')->toContain('MenuProviderInterface::class');
});










