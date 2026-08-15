<?php

declare(strict_types=1);
use Zoosper\Core\Http\Request;use Zoosper\Core\Http\Response;use Zoosper\Core\Routing\Router;
it('distinguishes 404 from 405 and emits Allow for static and parameter routes',function():void{$r=new Router();$r->post('/api/x',fn()=>Response::raw('ok'));$r->post('/admin/x/{id:\d+}',fn()=>Response::raw('ok'));$wrong=$r->dispatch(new Request('GET','/api/x'));expect($wrong->statusCode())->toBe(405)->and($wrong->headers()['Allow'])->toContain('POST')->and($r->dispatch(new Request('GET','/missing'))->statusCode())->toBe(404)->and($r->dispatch(new Request('GET','/admin/x/12'))->statusCode())->toBe(405);});
it('supports implicit HEAD without returning a body',function():void{$r=new Router();$r->get('/api/x',fn()=>Response::raw('body',200,['X-Test'=>'yes']));$response=$r->dispatch(new Request('HEAD','/api/x'));expect($response->statusCode())->toBe(200)->and($response->body())->toBe('')->and($response->headers()['X-Test'])->toBe('yes');});
