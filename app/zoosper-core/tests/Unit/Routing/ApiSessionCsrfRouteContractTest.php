<?php

declare(strict_types=1);

it('requires explicit CSRF policy for every session-authenticated API route',function():void{$root=dirname(__DIR__,5);$routes=require $root.'/app/zoosper-api/config/api_routes.php';$map=[];foreach($routes as $r)$map[$r['method'].' '.$r['path']]=$r;expect($map['POST /api/v1/auth/logout']['auth'])->toBe('session')->and($map['POST /api/v1/auth/logout']['csrf'])->toBe('enforce')->and($map['GET /api/v1/me']['auth'])->toBe('session')->and($map['GET /api/v1/me']['csrf'])->toBe('none');});
