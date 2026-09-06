<?php

declare(strict_types=1);

use Zoosper\Auth\AccountLockout\AdminAccountLockoutRepository;
use Zoosper\Auth\AccountLockout\AdminAccountLockoutService;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Service\AuthService;
use Zoosper\Auth\Service\PasswordHasher;

function c2EnforcementPdo(): \PDO
{
    $pdo = new \PDO('sqlite::memory:');
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE admin_users (id INTEGER PRIMARY KEY AUTOINCREMENT,email TEXT UNIQUE NOT NULL,name TEXT NOT NULL,password_hash TEXT NOT NULL,status TEXT NOT NULL,locale TEXT NULL,last_login_at TEXT NULL,created_at TEXT NULL,updated_at TEXT NULL)');
    $pdo->exec('CREATE TABLE admin_user_roles (user_id INTEGER NOT NULL,role_id INTEGER NOT NULL)');
    $pdo->exec('CREATE TABLE admin_roles (id INTEGER PRIMARY KEY,code TEXT,name TEXT)');
    $pdo->exec('CREATE TABLE admin_role_permissions (role_id INTEGER,permission_id INTEGER)');
    $pdo->exec('CREATE TABLE admin_permissions (id INTEGER PRIMARY KEY,code TEXT,name TEXT)');
    $pdo->exec('CREATE TABLE admin_account_lockouts (admin_user_id INTEGER PRIMARY KEY,failed_attempts INTEGER NOT NULL DEFAULT 0,locked_until TEXT NULL,last_failed_at TEXT NULL,updated_at TEXT NOT NULL)');
    $pdo->exec("INSERT INTO admin_roles(id,code,name) VALUES (1,'super_admin','Super Admin')");
    return $pdo;
}

it('locks known active accounts without persisting unknown or inactive failures', function (): void {
    $pdo=c2EnforcementPdo(); $users=new AdminUserRepository($pdo); $hasher=new PasswordHasher();
    $activeId=$users->createWithRoleIds('active@example.test','Active',$hasher->hash('CorrectPassword123!'),'active',[1]);
    $users->createWithRoleIds('inactive@example.test','Inactive',$hasher->hash('CorrectPassword123!'),'inactive',[1]);
    $lockouts=new AdminAccountLockoutService(new AdminAccountLockoutRepository($pdo),2,900,static fn():int=>1_800_000_000);
    $auth=new AuthService($users,$hasher,$lockouts);
    expect($auth->authenticate('missing@example.test','WrongPassword123!'))->toBeNull()
        ->and($auth->authenticate('inactive@example.test','WrongPassword123!'))->toBeNull()
        ->and((int)$pdo->query('SELECT COUNT(*) FROM admin_account_lockouts')->fetchColumn())->toBe(0)
        ->and($auth->authenticate('active@example.test','WrongPassword123!'))->toBeNull()
        ->and($auth->authenticate('active@example.test','WrongPassword123!'))->toBeNull()
        ->and($lockouts->isLocked($activeId))->toBeTrue()
        ->and($auth->authenticate('active@example.test','CorrectPassword123!'))->toBeNull();
});

it('clears prior failures after successful password authentication', function (): void {
    $pdo=c2EnforcementPdo(); $users=new AdminUserRepository($pdo); $hasher=new PasswordHasher();
    $id=$users->createWithRoleIds('admin@example.test','Admin',$hasher->hash('CorrectPassword123!'),'active',[1]);
    $repository=new AdminAccountLockoutRepository($pdo); $lockouts=new AdminAccountLockoutService($repository,3,900,static fn():int=>1_800_000_000);
    $auth=new AuthService($users,$hasher,$lockouts);
    expect($auth->authenticate('admin@example.test','WrongPassword123!'))->toBeNull()
        ->and($repository->find($id)?->failedAttempts)->toBe(1)
        ->and($auth->authenticate('admin@example.test','CorrectPassword123!'))->not->toBeNull()
        ->and($repository->find($id))->toBeNull();
});

it('keeps real password verification before neutral locked-account rejection', function (): void {
    $source=(string)file_get_contents(dirname(__DIR__,5).'/app/zoosper-auth/src/Service/AuthService.php');
    expect($source)->toContain('$passwordValid = $this->hasher->verify($password, $hashToCheck)')
        ->toContain('$locked = $this->lockouts?->isLocked($user->id) ?? false')
        ->toContain('if ($locked)')->not->toContain('Account locked')->not->toContain('locked until');
});
