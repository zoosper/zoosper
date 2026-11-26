<?php

declare(strict_types=1);
use Zoosper\Auth\Token\PersonalAccessTokenRepository;use Zoosper\Auth\Token\PersonalAccessTokenService;
function patPdo():PDO{$p=new PDO('sqlite::memory:');$p->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);$p->exec('CREATE TABLE personal_access_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT,public_id TEXT UNIQUE,admin_user_id INTEGER,name TEXT,token_hash TEXT,scopes_json TEXT,expires_at TEXT,last_used_at TEXT,revoked_at TEXT,created_at TEXT,updated_at TEXT)');return $p;}
it('issues a one-time plaintext token while storing only its hash',function():void{$p=patPdo();$result=(new PersonalAccessTokenService(new PersonalAccessTokenRepository($p)))->issue(7,'CI',['pages:read']);$row=$p->query('SELECT * FROM personal_access_tokens')->fetch(PDO::FETCH_ASSOC);expect($result['token'])->toStartWith('zp_pat_')->and($row['token_hash'])->toBe(hash('sha256',$result['token']))->and(json_encode($row))->not->toContain($result['token']);});
it('rejects unknown scopes',function():void{expect(fn()=>(new PersonalAccessTokenService(new PersonalAccessTokenRepository(patPdo())))->issue(7,'Bad',['root:all']))->toThrow(InvalidArgumentException::class);});

it('stores keyed scopes as a JSON list in their original order', function (): void {
    $pdo = patPdo();
    $repository = new PersonalAccessTokenRepository($pdo);
    $repository->create(7, 'public-id', 'CI', 'hash', ['first' => 'pages:read', 9 => 'pages:write'], null, '2026-01-01');

    $json = $pdo->query('SELECT scopes_json FROM personal_access_tokens')->fetchColumn();
    expect($json)->toBe('["pages:read","pages:write"]')
        ->and($repository->findByPublicId('public-id')->scopes)->toBe(['pages:read', 'pages:write']);
});










