<?php
declare(strict_types=1);
it('keeps menu admin controller thin and presentation outside the controller',function(){ $root=dirname(__DIR__,3);$source=(string)file_get_contents($root.'/src/Admin/Controller/MenuAdminController.php');expect($source)->not->toContain('<form')->not->toContain('<table')->toContain('MenuAdminResponder')->toContain('MenuAdminService');expect($root.'/resources/views/admin/menu/index.latte')->toBeFile()->and($root.'/resources/views/admin/menu/edit.latte')->toBeFile();});
