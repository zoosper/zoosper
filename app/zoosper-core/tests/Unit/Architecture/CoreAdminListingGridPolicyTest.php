<?php
declare(strict_types=1);
it('requires every first-party tabular Admin index listing to use the Admin Grid workspace',function():void{
 $root=dirname(__DIR__,5);
 $listings=[
  'admin.audit-log'=>$root.'/app/zoosper-admin/src/Controller/AuditLogController.php',
  'admin.login-history'=>$root.'/app/zoosper-admin/src/Controller/LoginHistoryController.php',
  'admin.users'=>$root.'/app/zoosper-auth/src/Admin/Grid/AdminUserGridIndex.php',
  'admin.roles'=>$root.'/app/zoosper-auth/src/Admin/Grid/RoleGridIndex.php',
  'admin.pages'=>$root.'/app/zoosper-page/src/Admin/PageAdminGridResponder.php',
  'admin.store-orders'=>$root.'/packages/zoosper-store-orders/src/Admin/StoreOrderGridWorkspace.php',
  'admin.access-tokens'=>$root.'/app/zoosper-auth/src/Admin/Controller/PersonalAccessTokenAdminController.php',
  'admin.sites'=>$root.'/app/zoosper-site/src/Admin/Controller/SiteAdminController.php',
  'admin.site-domains'=>$root.'/app/zoosper-site/src/Admin/Controller/SiteDomainAdminController.php',
  'admin.menus'=>$root.'/app/zoosper-menu/src/Admin/MenuAdminResponder.php',
  'admin.media'=>$root.'/packages/zoosper-media/src/Controller/MediaAdminController.php',
 ];
 foreach($listings as $key=>$file){expect($file)->toBeFile();$source=(string)file_get_contents($file);expect($source,$key.' must use the shared Admin Grid workspace')->toMatch('/AdminGrid|GridWorkspace|GridPageBuilder|GridIndex|OperationalGridPageBuilder/');}
});
it('forbids direct basic Grid renderer construction in first-party Admin listing controllers',function():void{
 $root=dirname(__DIR__,5);$violations=[];
 foreach([$root.'/app',$root.'/packages'] as $base){$iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base,FilesystemIterator::SKIP_DOTS));foreach($iterator as $file){$path=$file->getPathname();if(!$file->isFile()||!str_ends_with($path,'Controller.php'))continue;$source=(string)file_get_contents($path);if(str_contains($source,'function index(')&&str_contains($source,'new GridHtmlRenderer('))$violations[]=str_replace($root.'/','',$path);}}
 expect($violations)->toBe([]);
});










