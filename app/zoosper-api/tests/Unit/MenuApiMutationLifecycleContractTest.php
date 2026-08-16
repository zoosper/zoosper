<?php

declare(strict_types=1);

it('exposes stateless shared Menu mutations lifecycle and guarded deletion',function():void{
 $root=dirname(__DIR__,4);$routes=(string)file_get_contents($root.'/app/zoosper-api/config/api_routes.php');$controller=(string)file_get_contents($root.'/app/zoosper-api/src/Controller/MenuApiController.php');$services=(string)file_get_contents($root.'/app/zoosper-menu/config/services.php');
 foreach(["'action' => 'create'","'action' => 'update'","'action' => 'createItem'","'action' => 'updateItem'","'action' => 'deleteItem'","'action' => 'disable'","'action' => 'restore'","'action' => 'deletePermanently'"] as $action)expect($routes)->toContain($action);
 expect($controller)->toContain("allows('menus:write')")->toContain("can('menu.manage')")->toContain('$input[\'site_id\']=$siteId')->toContain('MenuMutationGuard')->toContain('MenuItemDeletionService')->toContain('MenuLifecycleCoordinator')->not->toContain('SessionGuard')
  ->and($services)->toContain('MenuMutationGuard::class')->toContain('Application\\{MenuAdminService,MenuItemDeletionService,MenuMutationGuard}');
});
it('moves guarded item deletion out of Admin ownership',function():void{$root=dirname(__DIR__,4);expect($root.'/app/zoosper-menu/src/Application/MenuItemDeletionService.php')->toBeFile()->and($root.'/app/zoosper-menu/src/Admin/Lifecycle/MenuItemDeletionService.php')->not->toBeFile();});
