<?php

declare(strict_types=1);
it('marks public read APIs stateless while session authentication remains stateful',function():void{$root=dirname(__DIR__,5);$api=require $root.'/app/zoosper-api/config/api_routes.php';$map=[];foreach($api as $r)$map[$r['method'].' '.$r['path']]=$r;expect($map['GET /api/v1/health']['stateless']??false)->toBeTrue()->and($map['GET /api/v1/hello']['stateless']??false)->toBeTrue()->and($map['GET /api/v1/content/page']['stateless']??false)->toBeTrue()->and($map['POST /api/v1/auth/login']['stateless']??false)->toBeFalse()->and($map['GET /api/v1/me']['stateless']??false)->toBeFalse();});
