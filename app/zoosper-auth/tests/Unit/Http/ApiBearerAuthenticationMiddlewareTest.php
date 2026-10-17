<?php

declare(strict_types=1);

namespace Zoosper\Auth\Tests\Unit\Http;

use PDO;
use Zoosper\Auth\Http\ApiBearerAuthenticationMiddleware;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Token\PersonalAccessTokenAuthenticator;
use Zoosper\Auth\Token\PersonalAccessTokenRepository;
use Zoosper\Auth\Token\PersonalAccessTokenService;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

beforeEach(function():void{
 $this->pdo=new PDO('sqlite::memory:');$this->pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
 $this->pdo->exec('CREATE TABLE admin_users(id INTEGER PRIMARY KEY,name TEXT,email TEXT,password_hash TEXT,status TEXT)');
 $this->pdo->exec('CREATE TABLE admin_roles(id INTEGER PRIMARY KEY,code TEXT,label TEXT)');
 $this->pdo->exec('CREATE TABLE admin_permissions(id INTEGER PRIMARY KEY,code TEXT,label TEXT,parent_code TEXT,sort_order INTEGER)');
 $this->pdo->exec('CREATE TABLE admin_role_permissions(role_id INTEGER,permission_id INTEGER)');
 $this->pdo->exec('CREATE TABLE admin_user_roles(user_id INTEGER,role_id INTEGER)');
 $this->pdo->exec('CREATE TABLE personal_access_tokens(id INTEGER PRIMARY KEY AUTOINCREMENT,public_id TEXT,admin_user_id INTEGER,name TEXT,token_hash TEXT,scopes_json TEXT,expires_at TEXT,last_used_at TEXT,revoked_at TEXT,created_at TEXT,updated_at TEXT)');
 $this->pdo->exec("INSERT INTO admin_users VALUES(1,'User','u@test','x','active')");
 $this->pdo->exec("INSERT INTO admin_roles VALUES(1,'editor','Editor')");
 $this->pdo->exec("INSERT INTO admin_permissions VALUES(1,'page.view','View',NULL,1)");
 $this->pdo->exec('INSERT INTO admin_role_permissions VALUES(1,1)');$this->pdo->exec('INSERT INTO admin_user_roles VALUES(1,1)');
 $users=new AdminUserRepository($this->pdo);$tokens=new PersonalAccessTokenRepository($this->pdo);$this->service=new PersonalAccessTokenService($tokens);$this->middleware=new ApiBearerAuthenticationMiddleware(new PersonalAccessTokenAuthenticator($tokens,$users),new JsonResponder());
});
function apiContext(string $scope,array $permissions):RouteContext{return new RouteContext('GET','/api/test',false,$permissions,true,'bearer',$scope);}
it('returns 401 and never invokes the handler for an anonymous bearer route request',function():void{$ran=false;$r=$this->middleware->process(new Request('GET','/api/test'),apiContext('pages:read',['page.view']),function()use(&$ran){$ran=true;return Response::json([]);});expect($r->statusCode())->toBe(401)->and($ran)->toBeFalse();});
it('returns 403 and never invokes the handler for the wrong scope',function():void{$issued=$this->service->issue(1,'x',['roles:read']);$ran=false;$r=$this->middleware->process(new Request('GET','/api/test',headers:['authorization'=>'Bearer '.$issued['token']]),apiContext('pages:read',['page.view']),function()use(&$ran){$ran=true;return Response::json([]);});expect($r->statusCode())->toBe(403)->and($ran)->toBeFalse();});
it('returns 403 and never invokes the handler for a missing owner permission',function():void{$issued=$this->service->issue(1,'x',['pages:read']);$ran=false;$r=$this->middleware->process(new Request('GET','/api/test',headers:['authorization'=>'Bearer '.$issued['token']]),apiContext('pages:read',['page.manage']),function()use(&$ran){$ran=true;return Response::json([]);});expect($r->statusCode())->toBe(403)->and($ran)->toBeFalse();});
it('invokes the handler only after scope and owner permission pass',function():void{$issued=$this->service->issue(1,'x',['pages:read']);$ran=false;$r=$this->middleware->process(new Request('GET','/api/test',headers:['authorization'=>'Bearer '.$issued['token']]),apiContext('pages:read',['page.view']),function()use(&$ran){$ran=true;return Response::json(['ok'=>true]);});expect($r->statusCode())->toBe(200)->and($ran)->toBeTrue();});
