<?php

declare(strict_types=1);

it('requires every API route to declare a coherent access classification',function():void{$root=dirname(__DIR__,5);$files=array_merge(glob($root.'/app/*/config/api_routes.php')?:[],glob($root.'/packages/*/config/api_routes.php')?:[]);expect($files)->not->toBeEmpty();foreach($files as $file){$routes=require $file;foreach($routes as $route){$auth=$route['auth']??null;expect($auth)->toBeIn(['anonymous','session','bearer'])->and((bool)($route['public']??false))->toBe($auth==='anonymous');if($auth==='bearer'){expect((bool)($route['stateless']??false))->toBeTrue()->and(array_key_exists('scope',$route))->toBeTrue()->and(array_key_exists('permission',$route))->toBeTrue();}}}});
