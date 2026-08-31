<?php
declare(strict_types=1);
it('keeps Media controls on Media routes and disables unsupported export',function():void{$root=dirname(__DIR__,3);$workspace=(string)file_get_contents($root.'/src/Admin/Grid/MediaVisualGridWorkspace.php');$controller=(string)file_get_contents($root.'/src/Controller/MediaAdminController.php');expect($workspace)->toContain('render($controlState,$action,null,false)')->not->toContain('/admin/pages');expect($controller)->toContain("adminUrl('media')")->toContain("adminUrl('media/upload')")->not->toContain('/admin/pages');});











