<?php

declare(strict_types=1);
it('exposes Menu lifecycle only through menu-managed POST routes',function(){ $root=dirname(__DIR__,3);$routes=require $root.'/config/admin_routes.php';$found=[];foreach($routes as $route){if(in_array($route['action']??'', ['disable','restore','deletePermanently'],true)){$found[$route['action']]=$route;}}expect($found)->toHaveKeys(['disable','restore','deletePermanently']);foreach($found as $route){expect($route['method'])->toBe('POST')->and($route['permission'])->toBe('menu.manage')->and($route['path'])->toContain('/admin/menus/{id:\d+}/');}});
it('keeps lifecycle and item-deletion policy outside the thin controller',function(){ $root=dirname(__DIR__,3);$controller=(string)file_get_contents($root.'/src/Admin/Controller/MenuAdminController.php');$lifecycle=(string)file_get_contents($root.'/src/Admin/Lifecycle/MenuLifecycleAdminResponder.php');expect($controller)->toContain('MenuLifecycleAdminResponder')->toContain('MenuItemDeletionService')->not->toContain('DELETE FROM menus')->not->toContain('beginTransaction(');expect($lifecycle)->toContain('actionsHtml(Menu $menu)')->toContain('_csrf_token')->not->toContain('onclick=')->not->toContain('confirm(');});
it('uses native disclosure for the Menu item workspace and removes direct whole-Menu deletion',function(){ $root=dirname(__DIR__,3);$template=(string)file_get_contents($root.'/resources/views/admin/menu/edit.latte');expect($template)->toContain('<details class="card menu-items-workspace"')->toContain('{$lifecycleHtml|noescape}')->not->toContain('<form method="post" action="{$deleteUrl}"');});










