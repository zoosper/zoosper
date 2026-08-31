<?php

declare(strict_types=1);
it('keeps the bearer identity endpoint public stateless and secret-free',function():void{$root=dirname(__DIR__,4);$routes=require $root.'/app/zoosper-api/config/api_routes.php';$route=array_values(array_filter($routes,fn(array $r)=>$r['path']==='/api/v1/token/me'))[0]??null;$source=(string)file_get_contents($root.'/app/zoosper-api/src/Controller/TokenMeController.php');expect($route)->not->toBeNull()->and($route['stateless'])->toBeTrue()->and($source)->toContain('token_public_id')->not->toContain('tokenHash')->not->toContain('Authorization');});










