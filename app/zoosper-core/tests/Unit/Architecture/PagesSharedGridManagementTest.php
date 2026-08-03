<?php

declare(strict_types=1);

it('wires Pages to shared saved-view management without owning renderer logic',function():void{
 $root=dirname(__DIR__,5);$controller=file_get_contents($root.'/app/zoosper-page/src/Admin/Controller/PageAdminController.php');$routes=require $root.'/app/zoosper-page/config/admin_routes.php';
 expect($controller)->toContain('GridWorkspaceMutationFormsRenderer')->toContain("'/admin/pages/grid'")->toContain('PageGridMutationCoordinator');
 expect($routes)->toContain(['method'=>'POST','path'=>'/admin/pages/grid','controller'=>Zoosper\Page\Admin\Controller\PageAdminController::class,'action'=>'gridMutation','permission'=>'page.manage']);
 expect($controller)->not->toContain('function renderSavedView');
});

it('anchors shared view management inside the Grid workspace boundary',function():void{
 $root=dirname(__DIR__,5);$js=file_get_contents($root.'/packages/zoosper-admin-grid/resources/admin/js/grid-workspace-command-bar.js');
 expect($js)->toContain("closest('[data-grid-workspace]')")->toContain('boundaryLeft')->toContain('boundaryRight');
});
