<?php

declare(strict_types=1);

namespace Zoosper\Auth\Tests\Unit\Http;

use PDO;
use Zoosper\Auth\Http\ApiSessionMiddleware;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

function apiSessionGuard(): SessionGuard {
 $pdo=new PDO('sqlite::memory:');$pdo->exec('CREATE TABLE admin_users(id INTEGER PRIMARY KEY,name TEXT,email TEXT,password_hash TEXT,status TEXT,is_active INTEGER,locale TEXT)');$pdo->exec('CREATE TABLE admin_user_roles(user_id INTEGER,role_id INTEGER)');$pdo->exec('CREATE TABLE admin_role_permissions(role_id INTEGER,permission_id INTEGER)');$pdo->exec('CREATE TABLE admin_permissions(id INTEGER,code TEXT)');return new SessionGuard(new AdminUserRepository($pdo),0);
}
function apiSessionContext(string $csrf='none'): RouteContext{return new RouteContext('GET','/api/v1/me',false,null,false,'session',null,$csrf);}
beforeEach(fn()=>$_SESSION=[]);afterEach(fn()=>$_SESSION=[]);
it('returns JSON 401 and does not invoke the handler for an anonymous session route',function():void{$ran=false;$r=(new ApiSessionMiddleware(apiSessionGuard(),new JsonResponder()))->process(new Request('GET','/api/v1/me'),apiSessionContext(),function()use(&$ran){$ran=true;return Response::json([]);});expect($r->statusCode())->toBe(401)->and($r->headers()['Content-Type'])->toBe('application/json; charset=utf-8')->and($ran)->toBeFalse();});
it('invokes the handler for an authenticated session route',function():void{$guard=apiSessionGuard();$guard->login(new AdminUser(1,'a@test','Admin','hash','active',[]));$ran=false;$r=(new ApiSessionMiddleware($guard,new JsonResponder()))->process(new Request('GET','/api/v1/me'),apiSessionContext(),function()use(&$ran){$ran=true;return Response::json(['ok'=>true]);});expect($r->statusCode())->toBe(200)->and($ran)->toBeTrue();});
