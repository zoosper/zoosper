<?php
declare(strict_types=1);
it('hardens menu forms with csrf selectors configurable urls and item mutations',function(){ $root=dirname(__DIR__,3);$template=(string)file_get_contents($root.'/resources/views/admin/menu/edit.latte');$controller=(string)file_get_contents($root.'/src/Admin/Controller/MenuAdminController.php');$responder=(string)file_get_contents($root.'/src/Admin/MenuAdminResponder.php');expect($template)->toContain('name="_csrf_token"')->toContain('{foreach $sites as $site}')->toContain('{foreach $pages as $page}')->toContain('/delete')->and($controller)->not->toContain("'/admin/")->toContain('updateItem')->toContain('deleteItem')->and($responder)->toContain('AdminUrlGenerator')->toContain('CsrfTokenManager');});










