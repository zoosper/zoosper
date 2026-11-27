<?php

declare(strict_types=1);

namespace Zoosper\Auth\Tests\Unit\Service;

use PDO;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Service\SessionGuard;

beforeEach(function (): void {
    $_SESSION = [];
});

afterEach(function (): void {
    $_SESSION = [];
});

function pendingCredentialUsers(PDO $pdo): AdminUserRepository
{
    $pdo->exec('CREATE TABLE admin_users (id INTEGER PRIMARY KEY, name TEXT, email TEXT, password_hash TEXT, status TEXT, created_at TEXT, updated_at TEXT)');
    $pdo->exec('CREATE TABLE admin_user_roles (user_id INTEGER, role_id INTEGER)');
    $pdo->exec('CREATE TABLE admin_role_permissions (role_id INTEGER, permission_id INTEGER)');
    $pdo->exec('CREATE TABLE admin_permissions (id INTEGER PRIMARY KEY, code TEXT, name TEXT)');
    $pdo->exec("INSERT INTO admin_users (id, name, email, password_hash, status) VALUES (1, 'Admin', 'admin@example.test', 'old-hash', 'active')");

    return new AdminUserRepository($pdo);
}

it('rejects a pending challenge after the verified password has changed', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $users = pendingCredentialUsers($pdo);
    $guard = new SessionGuard($users);
    $guard->beginTwoFactorChallenge($users->findById(1));

    $users->updatePassword(1, 'new-hash');
    $currentUser = $users->findById(1);
    expect($guard->completeTwoFactorChallenge($currentUser))->toBeFalse()
        ->and($guard->user())->toBeNull()
        ->and($guard->pendingTwoFactorUserId())->toBeNull();
});

it('rejects a pending challenge for a deactivated account', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $users = pendingCredentialUsers($pdo);
    $guard = new SessionGuard($users);
    $guard->beginTwoFactorChallenge($users->findById(1));

    $pdo->exec("UPDATE admin_users SET status = 'inactive' WHERE id = 1");
    $currentUser = $users->findById(1);
    expect($guard->completeTwoFactorChallenge($currentUser))->toBeFalse()
        ->and($guard->user())->toBeNull();
});

it('rejects legacy pending challenges without a credential fingerprint', function (): void {
    $users = new AdminUserRepository(new PDO('sqlite::memory:'));
    $guard = new SessionGuard($users);
    $user = new AdminUser(1, 'admin@example.test', 'Admin', 'hash', 'active', []);
    $guard->beginTwoFactorChallenge($user);
    unset($_SESSION['admin_password_hash_fingerprint']);

    expect($guard->completeTwoFactorChallenge($user))->toBeFalse();
});

it('promotes a pending challenge when the verified credentials remain current', function (): void {
    $users = new AdminUserRepository(new PDO('sqlite::memory:'));
    $guard = new SessionGuard($users);
    $user = new AdminUser(1, 'admin@example.test', 'Admin', 'hash', 'active', []);
    $guard->beginTwoFactorChallenge($user);

    expect($guard->completeTwoFactorChallenge($user))->toBeTrue()
        ->and($guard->user())->toBe($user)
        ->and($guard->pendingTwoFactorUserId())->toBeNull();
});