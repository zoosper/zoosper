<?php
declare(strict_types=1);
use Zoosper\Admin\Audit\Grid\AuditLogGridDefinition;
use Zoosper\Admin\Audit\Grid\LoginHistoryGridDefinition;
it('cuts Audit Log and Login History to persistent Admin Grid workspaces',function():void{
 $admin=dirname(__DIR__,4);
 expect(AuditLogGridDefinition::KEY)->toBe('admin.audit-log')->and(LoginHistoryGridDefinition::KEY)->toBe('admin.login-history');
 foreach(['AuditLogController.php','LoginHistoryController.php'] as $file){
  $source=(string)file_get_contents($admin.'/src/Controller/'.$file);
  expect($source)->toContain('OperationalGridPageBuilder')->toContain('workspaceHtml');
  expect($source)->not->toContain('new GridHtmlRenderer');
  expect($source)->not->toContain('private function queryValues');
 }
 foreach(['audit-log','login-history'] as $view){$source=(string)file_get_contents($admin.'/resources/views/'.$view.'/index.php');expect($source)->toContain('$workspaceHtml')->toContain('$gridHtml');}
});
it('keeps generic Grid and Admin Grid as complementary packages',function():void{
 $root=dirname(__DIR__,6);
 expect($root.'/packages/zoosper-grid/composer.json')->toBeFile()->and($root.'/packages/zoosper-admin-grid/composer.json')->toBeFile();
});
