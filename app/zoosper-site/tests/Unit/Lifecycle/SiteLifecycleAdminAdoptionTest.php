<?php

declare(strict_types=1);

it('exposes Site lifecycle only through settings-managed POST routes',function(){ $root=dirname(__DIR__,3);$routes=require $root.'/config/admin_routes.php';$found=[];foreach($routes as $route){if(in_array($route['action']??'', ['disable','restore','deletePermanently'],true)){$found[$route['action']]=$route;}}expect($found)->toHaveKeys(['disable','restore','deletePermanently']);foreach($found as $route){expect($route['method'])->toBe('POST')->and($route['permission'])->toBe('settings.manage')->and($route['path'])->toContain('/admin/sites/{id:\d+}/');}});
it('keeps destructive persistence out of SiteAdminController',function(){ $root=dirname(__DIR__,3);$controller=(string)file_get_contents($root.'/src/Admin/Controller/SiteAdminController.php');$responder=(string)file_get_contents($root.'/src/Admin/Lifecycle/SiteLifecycleAdminResponder.php');expect($controller)->toContain('SiteLifecycleAdminResponder')->toContain('lifecycleOperation(')->not->toContain('DELETE FROM sites')->not->toContain('beginTransaction(');expect($responder)->toContain('actionsHtml(Site $site)')->toContain('_csrf_token')->not->toContain('onclick=')->not->toContain('confirm(');});










