<?php

declare(strict_types=1);

namespace Zoosper\Auth\Tests\Unit\Service;

use PDO;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Service\PasswordHasher;
use Zoosper\Auth\Service\SessionGuard;

beforeEach(function (): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
    } else {
        $_SESSION = [];
    }
});

function memoryUserRepo(PDO $pdo): AdminUserRepository
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS admin_users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT UNIQUE NOT NULL,
        name TEXT NOT NULL,
        password_hash TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT "active",
        locale TEXT NULL,
        created_at TEXT NULL,
        updated_at TEXT NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS admin_user_roles (
        user_id INTEGER NOT NULL,
        role_id INTEGER NOT NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS admin_roles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT UNIQUE NOT NULL,
        name TEXT NOT NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS admin_role_permissions (
        role_id INTEGER NOT NULL,
        permission_id INTEGER NOT NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS admin_permissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT UNIQUE NOT NULL,
        name TEXT NOT NULL
    )');

    $pdo->exec('INSERT INTO admin_roles (id, code, name) VALUES (1, "super_admin", "Super Admin")');

    return new AdminUserRepository($pdo);
}

it('stores password hash fingerprint on login and invalidates session when password changes', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $users = memoryUserRepo($pdo);
    $hasher = new PasswordHasher();

    $initialHash = $hasher->hash('InitialSecret123!');
    $userId = $users->createWithRoleIds('admin@example.test', 'Admin User', $initialHash, 'active', [1]);
    $user = $users->findById($userId);
    expect($user)->not->toBeNull();

    $guard = new SessionGuard($users, 7200);
    $guard->login($user);

    expect($_SESSION['admin_password_hash_fingerprint'] ?? null)->toBe(hash('sha256', $initialHash));

    $guard->clearUserCache();
    expect($guard->user())->not->toBeNull()
        ->and($guard->user()->id)->toBe($userId);

    // Simulate password change on another device/session
    $newHash = $hasher->hash('NewSecretPassword456!');
    $users->updatePassword($userId, $newHash);

    // Reset memoized user cache for subsequent request
    $guard->clearUserCache();

    // The existing session with old fingerprint must be invalidated and user() returns null
    expect($guard->user())->toBeNull()
        ->and($_SESSION['admin_user_id'] ?? null)->toBeNull();
});

it('preserves authenticated session when password fingerprint is refreshed', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $users = memoryUserRepo($pdo);
    $hasher = new PasswordHasher();

    $initialHash = $hasher->hash('InitialSecret123!');
    $userId = $users->createWithRoleIds('admin@example.test', 'Admin User', $initialHash, 'active', [1]);
    $user = $users->findById($userId);
    expect($user)->not->toBeNull();

    $guard = new SessionGuard($users, 7200);
    $guard->login($user);

    $newHash = $hasher->hash('NewSecretPassword456!');
    $users->updatePassword($userId, $newHash);
    $guard->refreshPasswordHashFingerprint($newHash);

    $guard->clearUserCache();
    expect($guard->user())->not->toBeNull()
        ->and($guard->user()->id)->toBe($userId);
});










