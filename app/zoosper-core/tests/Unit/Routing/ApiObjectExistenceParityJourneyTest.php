<?php

declare(strict_types=1);

use Zoosper\Auth\Api\RoleApiController;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Repository\RoleRepository;
use Zoosper\Auth\Token\BearerPrincipalContext;
use Zoosper\Auth\Token\PersonalAccessTokenAuthenticator;
use Zoosper\Auth\Token\PersonalAccessTokenRepository;
use Zoosper\Auth\Token\PersonalAccessTokenService;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Site\SiteContext;
use Zoosper\Page\Api\PageApiController;
use Zoosper\Page\Api\PageLifecycleApiResponder;
use Zoosper\Page\Application\Publication\PagePublicationCoordinator;
use Zoosper\Page\Application\Save\PageSaveCoordinator;
use Zoosper\Page\Content\DocumentNormalizer;
use Zoosper\Page\Content\DocumentValidator;
use Zoosper\Page\Lifecycle\PageLifecycleCoordinator;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Service\PageRevisionService;

/** @return array{pdo:PDO,auth:PersonalAccessTokenAuthenticator,service:PersonalAccessTokenService} */
function objectParityAuthFixture(array $permissions): array
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE admin_users(id INTEGER PRIMARY KEY,name TEXT,email TEXT,password_hash TEXT,status TEXT)');
    $pdo->exec('CREATE TABLE admin_roles(id INTEGER PRIMARY KEY,code TEXT,label TEXT,created_at TEXT,updated_at TEXT)');
    $pdo->exec('CREATE TABLE admin_permissions(id INTEGER PRIMARY KEY,code TEXT,label TEXT,parent_code TEXT,sort_order INTEGER)');
    $pdo->exec('CREATE TABLE admin_role_permissions(role_id INTEGER,permission_id INTEGER)');
    $pdo->exec('CREATE TABLE admin_user_roles(user_id INTEGER,role_id INTEGER)');
    $pdo->exec('CREATE TABLE personal_access_tokens(id INTEGER PRIMARY KEY AUTOINCREMENT,public_id TEXT,admin_user_id INTEGER,name TEXT,token_hash TEXT,scopes_json TEXT,expires_at TEXT,last_used_at TEXT,revoked_at TEXT,created_at TEXT,updated_at TEXT)');
    $pdo->exec("INSERT INTO admin_users VALUES(1,'Parity User','parity@test','x','active')");
    $pdo->exec("INSERT INTO admin_roles(id,code,label) VALUES(1,'parity','Parity')");
    $pdo->exec('INSERT INTO admin_user_roles VALUES(1,1)');
    foreach (array_values($permissions) as $index => $permission) {
        $id = $index + 1;
        $statement = $pdo->prepare('INSERT INTO admin_permissions VALUES(:id,:code,:label,NULL,:sort)');
        $statement->execute(['id'=>$id,'code'=>$permission,'label'=>$permission,'sort'=>$id]);
        $pdo->prepare('INSERT INTO admin_role_permissions VALUES(1,:id)')->execute(['id'=>$id]);
    }
    $users = new AdminUserRepository($pdo); $tokens = new PersonalAccessTokenRepository($pdo);
    return ['pdo'=>$pdo,'auth'=>new PersonalAccessTokenAuthenticator($tokens,$users),'service'=>new PersonalAccessTokenService($tokens)];
}

/** @template T of object @param class-string<T> $class @return T */
function unusedObject(string $class): object
{
    return (new ReflectionClass($class))->newInstanceWithoutConstructor();
}

function paritySiteContext(int $siteId): SiteContext
{
    return new SiteContext('main','Main','main','Main','default','Default','en_AU','AUD','https://main.test','',$siteId);
}

it('makes a cross-Site existing Page indistinguishable from an absent Page', function (): void {
    $fixture = objectParityAuthFixture(['page.view']);
    $pdo = $fixture['pdo'];
    $pdo->exec('CREATE TABLE pages(id INTEGER PRIMARY KEY AUTOINCREMENT,site_id INTEGER,title TEXT,slug TEXT,content TEXT,status TEXT,created_by INTEGER,updated_by INTEGER,created_at TEXT,updated_at TEXT,published_at TEXT)');
    $pages = new PageRepository($pdo);
    $foreignPageId = $pages->create(2,'Foreign','foreign','body','draft',1);
    $principals = new BearerPrincipalContext();
    $controller = new PageApiController(
        new JsonResponder(), $principals, $pages,
        unusedObject(PageSaveCoordinator::class), new DocumentNormalizer(new DocumentValidator()),
        unusedObject(PagePublicationCoordinator::class), unusedObject(PageRevisionService::class),
        unusedObject(PageLifecycleCoordinator::class), new PageLifecycleApiResponder(new JsonResponder()),
    );
    $issued = $fixture['service']->issue(1,'page parity',['pages:read']);
    $request = static fn (int $id): Request => (new Request('GET','/api/v1/pages/'.$id,headers:['authorization'=>'Bearer '.$issued['token']]))
        ->withSiteContext(paritySiteContext(1))->withRouteParams(['id'=>(string)$id]);
    $foreignRequest = $request($foreignPageId);
    $principals->establish($foreignRequest, $fixture['auth']->authenticate($foreignRequest->header('authorization')) ?? throw new RuntimeException('Fixture authentication failed.'));
    $foreign = $controller->show($foreignRequest);
    $missingRequest = $request(999999);
    $principals->establish($missingRequest, $fixture['auth']->authenticate($missingRequest->header('authorization')) ?? throw new RuntimeException('Fixture authentication failed.'));
    $missing = $controller->show($missingRequest);
    expect($foreign->statusCode())->toBe(404)
        ->and($missing->statusCode())->toBe(404)
        ->and($foreign->body())->toBe($missing->body())
        ->and($foreign->headers())->toBe($missing->headers())
        ->and(json_decode($foreign->body(),true)['error'])->toBe([
            'code'=>'page_not_found','message'=>'Page does not exist for this Site.','meta'=>[],
        ]);
});

it('documents authorised global Role existence semantics without applying Site concealment', function (): void {
    $fixture = objectParityAuthFixture(['role.view']);
    $pdo = $fixture['pdo'];
    $pdo->exec("UPDATE admin_roles SET created_at='2026-01-01',updated_at='2026-01-01' WHERE id=1");
    $principals = new BearerPrincipalContext();
    $controller = new RoleApiController(new JsonResponder(),$principals,new RoleRepository($pdo));
    $issued = $fixture['service']->issue(1,'role semantics',['roles:read']);
    $request = static fn (int $id): Request => (new Request('GET','/api/v1/roles/'.$id,headers:['authorization'=>'Bearer '.$issued['token']]))->withRouteParams(['id'=>(string)$id]);
    $existingRequest = $request(1);
    $principals->establish($existingRequest, $fixture['auth']->authenticate($existingRequest->header('authorization')) ?? throw new RuntimeException('Fixture authentication failed.'));
    $existing = $controller->show($existingRequest);
    $missingRequest = $request(999999);
    $principals->establish($missingRequest, $fixture['auth']->authenticate($missingRequest->header('authorization')) ?? throw new RuntimeException('Fixture authentication failed.'));
    $missing = $controller->show($missingRequest);
    expect($existing->statusCode())->toBe(200)
        ->and(json_decode($existing->body(),true)['data']['role']['id'])->toBe(1)
        ->and($missing->statusCode())->toBe(404)
        ->and(json_decode($missing->body(),true)['error']['code'])->toBe('role_not_found');
});
