<?php
declare(strict_types=1);
it('renders a loud Marko error instead of raw exception text',function(){
 $root=dirname(__DIR__,3);
 $controller=(string)file_get_contents($root.'/src/Admin/Controller/MenuAdminController.php');
 $responder=(string)file_get_contents($root.'/src/Admin/MenuAdminResponder.php');
 $view=(string)file_get_contents($root.'/resources/views/admin/menu/error.latte');
 expect($controller)->toContain('$this->views->error')->not->toContain('Response::html($e->getMessage()')
  ->and($responder)->toContain('zoosper-menu::admin/menu/error.latte')
  ->and($view)->toContain('Marko runtime error')->toContain('role="alert"')->toContain('Technical details')->toContain('menu-runtime-error')->not->toContain(' style=');
});










