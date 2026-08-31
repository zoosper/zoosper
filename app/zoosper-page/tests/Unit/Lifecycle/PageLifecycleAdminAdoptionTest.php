<?php

declare(strict_types=1);

it('exposes Page lifecycle mutations only as protected POST routes', function (): void {
    $root=dirname(__DIR__,5); $routes=require $root.'/app/zoosper-page/config/admin_routes.php';
    $found=[]; foreach($routes as $route){ if(in_array($route['action']??'',['archive','restore','deletePermanently'],true)){$found[$route['action']]=$route;} }
    expect($found)->toHaveKeys(['archive','restore','deletePermanently']);
    foreach($found as $route){ expect($route['method'])->toBe('POST')->and($route['permission'])->toBe('page.manage')->and($route['path'])->toContain('/admin/pages/{id:\d+}/'); }
});

it('keeps lifecycle orchestration out of the thin Page controller', function (): void {
    $root=dirname(__DIR__,5);
    $controller=(string)file_get_contents($root.'/app/zoosper-page/src/Admin/Controller/PageAdminController.php');
    $responder=(string)file_get_contents($root.'/app/zoosper-page/src/Admin/Lifecycle/PageLifecycleAdminResponder.php');
    expect($controller)->toContain('PageLifecycleAdminResponder')->toContain('lifecycleOperation(')
        ->not->toContain('beginTransaction(')->not->toContain('DELETE FROM pages')->not->toContain('PageReferenceInspector');
    expect($responder)->toContain('actionsHtml(Page $page)')->toContain('PageLifecycleCoordinator')->toContain('_csrf_token');
});

it('renders contextual CSP-safe lifecycle actions without inline confirmation handlers', function (): void {
    $root=dirname(__DIR__,5); $source=(string)file_get_contents($root.'/app/zoosper-page/src/Admin/Lifecycle/PageLifecycleAdminResponder.php');
    expect($source)->toContain('Archive page')->toContain('Restore to draft')->toContain('Delete permanently')
        ->toContain('button--danger')->not->toContain('onclick=')->not->toContain('onsubmit=')->not->toContain('window.confirm');
});

it('adds archived Pages to the Grid status filter and wires the responder explicitly', function (): void {
    $root=dirname(__DIR__,5);
    $grid=(string)file_get_contents($root.'/app/zoosper-page/src/Admin/PageGridDefinition.php');
    $factory=(string)file_get_contents($root.'/app/zoosper-page/config/controllers.php');
    expect($grid)->toContain("['value'=>'archived','label'=>'Archived']")
        ->and($factory)->toContain('lifecycleResponder: new PageLifecycleAdminResponder(')
        ->toContain('$services->get(PageLifecycleCoordinator::class)');
});










