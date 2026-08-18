<?php
declare(strict_types=1);
it('owns stateless archive restore and delete routes in Page', function (): void {
 $root=dirname(__DIR__,5);$routes=require $root.'/app/zoosper-page/config/api_routes.php';$actions=[];foreach($routes as $r){if(in_array($r['action']??'', ['archive','restoreArchived','deletePermanently'],true))$actions[$r['action']]=$r;}
 expect(array_keys($actions))->toEqualCanonicalizing(['archive','restoreArchived','deletePermanently']);foreach($actions as $route)expect($route['stateless'])->toBeTrue()->and($route['public'])->toBeTrue();expect($actions['deletePermanently']['method'])->toBe('DELETE');
});
it('requires dedicated lifecycle scopes and current Page management permission', function (): void {
 $root=dirname(__DIR__,5);$source=(string)file_get_contents($root.'/app/zoosper-page/src/Api/PageApiController.php');expect($source)->toContain("principal(\$request, 'pages:archive')")->toContain("principal(\$request, 'pages:delete')")->toContain('$this->sitePage($request)')->toContain('$this->lifecycle->archive(')->toContain('$this->lifecycle->restore(')->toContain('$this->lifecycle->deletePermanently(')->not->toContain('tokenHash');
});
it('maps lifecycle blockers without exposing content or credentials', function (): void {
 $root=dirname(__DIR__,5);$source=(string)file_get_contents($root.'/app/zoosper-page/src/Api/PageLifecycleApiResponder.php');expect($source)->toContain("'blockers' => \$result->blockers")->toContain("'page_lifecycle_conflict'")->not->toContain('content')->not->toContain('token')->not->toContain('authorization');
});
