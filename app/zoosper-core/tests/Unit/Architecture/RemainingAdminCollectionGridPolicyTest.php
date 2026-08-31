<?php
declare(strict_types=1);it('cuts remaining tabular Admin collections to stable Grid identities and server pagination',function():void{$root=dirname(__DIR__,5);$files=['admin.access-tokens'=>'app/zoosper-auth/src/Admin/Grid/AccessToken/AccessTokenGrid.php','admin.sites'=>'app/zoosper-site/src/Admin/Grid/SiteGrid.php','admin.site-domains'=>'app/zoosper-site/src/Admin/Grid/SiteDomainGrid.php','admin.menus'=>'app/zoosper-menu/src/Admin/Grid/MenuGrid.php'];foreach($files as $key=>$file){$s=(string)file_get_contents($root.'/'.$file);expect($s)->toContain("KEY='$key'")->toContain('COUNT(*)')->toContain('LIMIT :limit OFFSET :offset')->toContain('GridDataSourceInterface');}});it('keeps Menu item editing specialised while replacing only the collection index',function():void{$root=dirname(__DIR__,5);$controller=(string)file_get_contents($root.'/app/zoosper-menu/src/Admin/Controller/MenuAdminController.php');expect($controller)->toContain('addItem(')->toContain('updateItem(')->toContain('deleteItem(');});










