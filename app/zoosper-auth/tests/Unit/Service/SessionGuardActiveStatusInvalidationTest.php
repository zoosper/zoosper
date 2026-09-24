<?php

declare(strict_types=1);

namespace Zoosper\Auth\Tests\Unit\Service;

use PDO;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Service\SessionGuard;

beforeEach(function (): void {
    $_SESSION = [];
});

afterEach(function (): void {
    $_SESSION = [];
});

it('rejects and clears an existing session after the admin account is deactivated', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE admin_users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT, password_hash TEXT, status TEXT, is_active INTEGER, created_at TEXT, updated_at TEXT)');
    $pdo->exec('CREATE TABLE admin_user_roles (user_id INTEGER, role_id INTEGER)');
    $pdo->exec('CREATE TABLE admin_role_permissions (role_id INTEGER, permission_id INTEGER)');
    $pdo->exec('CREATE TABLE admin_permissions (id INTEGER PRIMARY KEY, code TEXT, name TEXT, group_name TEXT)');
    $pdo->exec("INSERT INTO admin_users (id, name, email, password_hash, status, is_active) VALUES (1, 'Admin', 'admin@example.com', 'stored-hash', 'active', 1)");

    $users = new AdminUserRepository($pdo);
    $user = $users->findById(1);
    expect($user)->not->toBeNull();

    $loginGuard = new SessionGuard($users);
    $loginGuard->login($user);
    expect($_SESSION)->toHaveKey('admin_user_id', 1)
        ->and($_SESSION)->toHaveKey('admin_password_hash_fingerprint');

    $pdo->exec("UPDATE admin_users SET status = 'inactive', is_active = 0 WHERE id = 1");

    $nextRequestGuard = new SessionGuard($users);
    expect($nextRequestGuard->user())->toBeNull()
        ->and($_SESSION)->toBe([]);
});

it('continues to resolve an active account with a matching password fingerprint', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE admin_users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT, password_hash TEXT, status TEXT, is_active INTEGER, created_at TEXT, updated_at TEXT)');
    $pdo->exec('CREATE TABLE admin_user_roles (user_id INTEGER, role_id INTEGER)');
    $pdo->exec('CREATE TABLE admin_role_permissions (role_id INTEGER, permission_id INTEGER)');
    $pdo->exec('CREATE TABLE admin_permissions (id INTEGER PRIMARY KEY, code TEXT, name TEXT, group_name TEXT)');
    $pdo->exec("INSERT INTO admin_users (id, name, email, password_hash, status, is_active) VALUES (1, 'Admin', 'admin@example.com', 'stored-hash', 'active', 1)");

    $users = new AdminUserRepository($pdo);
    $user = $users->findById(1);
    expect($user)->not->toBeNull();

    (new SessionGuard($users))->login($user);
    $nextRequestGuard = new SessionGuard($users);

    expect($nextRequestGuard->user())->not->toBeNull()
        ->and($nextRequestGuard->user()?->id)->toBe(1);
});
