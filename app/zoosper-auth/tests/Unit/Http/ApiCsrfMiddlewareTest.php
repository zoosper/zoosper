<?php

declare(strict_types=1);

namespace Zoosper\Auth\Tests\Unit\Http;

use Zoosper\Auth\Http\ApiCsrfMiddleware;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

function apiCsrfContext(string $policy='enforce'):RouteContext{return new RouteContext('POST','/api/v1/auth/logout',false,null,false,'session',null,$policy);}
beforeEach(fn()=>$_SESSION=[]);afterEach(fn()=>$_SESSION=[]);
it('returns JSON 419 and does not invoke logout for a missing token',function():void{$ran=false;$r=(new ApiCsrfMiddleware(new CsrfTokenManager(),new JsonResponder()))->process(new Request('POST','/api/v1/auth/logout'),apiCsrfContext(),function()use(&$ran){$ran=true;return Response::json([]);});expect($r->statusCode())->toBe(419)->and($r->headers()['Content-Type'])->toBe('application/json; charset=utf-8')->and($ran)->toBeFalse();});
it('returns JSON 419 for an invalid token',function():void{$csrf=new CsrfTokenManager();$csrf->token();$r=(new ApiCsrfMiddleware($csrf,new JsonResponder()))->process(new Request('POST','/api/v1/auth/logout',headers:['x-csrf-token'=>'wrong']),apiCsrfContext(),fn()=>Response::json([]));expect($r->statusCode())->toBe(419);});
it('accepts a valid header token and invokes logout',function():void{$csrf=new CsrfTokenManager();$token=$csrf->token();$ran=false;$r=(new ApiCsrfMiddleware($csrf,new JsonResponder()))->process(new Request('POST','/api/v1/auth/logout',headers:['x-csrf-token'=>$token]),apiCsrfContext(),function()use(&$ran){$ran=true;return Response::json(['ok'=>true]);});expect($r->statusCode())->toBe(200)->and($ran)->toBeTrue();});
it('does not enforce CSRF on a read-only session route',function():void{$ran=false;(new ApiCsrfMiddleware(new CsrfTokenManager(),new JsonResponder()))->process(new Request('GET','/api/v1/me'),new RouteContext('GET','/api/v1/me',false,null,false,'session',null,'none'),function()use(&$ran){$ran=true;return Response::json([]);});expect($ran)->toBeTrue();});
