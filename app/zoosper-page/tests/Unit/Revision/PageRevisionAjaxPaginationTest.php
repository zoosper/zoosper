<?php

declare(strict_types=1);

it('serves Page revision pagination through a protected fragment endpoint', function (): void {
    $root=dirname(__DIR__,5); $routes=require $root.'/app/zoosper-page/config/admin_routes.php';
    $route=null; foreach($routes as $candidate){ if(($candidate['action']??'')==='revisionHistory'){$route=$candidate;break;} }
    expect($route)->not->toBeNull()
        ->and($route['method'])->toBe('GET')
        ->and($route['permission'])->toBe('page.manage')
        ->and($route['path'])->toBe('/admin/pages/{id:\d+}/revisions');
});

it('progressively enhances revision pagination without removing link fallback', function (): void {
    $root=dirname(__DIR__,5);
    $responder=(string)file_get_contents($root.'/app/zoosper-page/src/Admin/PageRevisionAdminResponder.php');
    $controller=(string)file_get_contents($root.'/app/zoosper-page/src/Admin/Controller/PageAdminController.php');
    $js=(string)file_get_contents($root.'/public/assets/page/js/page-revision-pagination.js');
    expect($responder)->toContain('data-page-revision-history')->toContain('data-page-revision-results')
        ->toContain('historyFragment(Page $page, int $currentPage)')
        ->toContain('pages/{$page->id}/revisions')
        ->and($controller)->toContain('revisionHistory(Request $request)')
        ->and($js)->toContain('window.fetch(link.href')
        ->toContain("credentials: 'same-origin'")
        ->toContain('window.history.replaceState')
        ->toContain('window.location.assign(link.href)')
        ->not->toContain('onclick=');
});

it('registers the revision pagination script through the Page Admin asset manifest', function (): void {
    $root=dirname(__DIR__,5); $source=(string)file_get_contents($root.'/app/zoosper-page/config/admin_assets.php');
    expect($source)->toContain('/assets/page/js/page-revision-pagination.js');
});










