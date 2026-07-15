<?php

declare(strict_types=1);

namespace Zoosper\Auth\Tests\Unit\Repository;

/**
 * Regression tests locking in the HY093 locale-binding fix.
 *
 * Phase 1.26a-fix. createWithRoleIds() and updateUser() previously referenced
 * the :locale placeholder in SQL but did not bind a value for it, causing PDO
 * HY093. These tests use an in-memory SQLite database to prove locale is bound
 * and persisted on both create and update, and can be cleared to null.
 *
 * Skips automatically if pdo_sqlite is unavailable.
 *
 * PCI-aware: uses only non-sensitive placeholder data.
 */

use PDO;
use Zoosper\Auth\Repository\AdminUserRepository;

/**
 * Build an in-memory SQLite PDO with the minimal admin schema for these tests.
 */
function makeAuthPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec("CREATE TABLE admin_users (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL UNIQUE, locale TEXT NULL, name TEXT NOT NULL, password_hash TEXT NOT NULL, status TEXT NOT NULL DEFAULT 'active', last_login_at TEXT NULL, created_at TEXT NOT NULL, updated_at TEXT NOT NULL)");
    $pdo->exec("CREATE TABLE admin_roles (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT NOT NULL UNIQUE, label TEXT NOT NULL, created_at TEXT NOT NULL, updated_at TEXT NOT NULL)");
    $pdo->exec("CREATE TABLE admin_user_roles (user_id INTEGER NOT NULL, role_id INTEGER NOT NULL, PRIMARY KEY(user_id, role_id))");
    $pdo->exec("CREATE TABLE admin_permissions (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT NOT NULL UNIQUE, label TEXT NOT NULL, created_at TEXT NOT NULL)");
    $pdo->exec("CREATE TABLE admin_role_permissions (role_id INTEGER NOT NULL, permission_id INTEGER NOT NULL, PRIMARY KEY(role_id, permission_id))");
    $pdo->exec("INSERT INTO admin_roles (code, label, created_at, updated_at) VALUES ('super_admin', 'Super Admin', '2026-01-01 00:00:00', '2026-01-01 00:00:00')");

    return $pdo;
}

test('createWithRoleIds binds and persists locale', function () {
    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('pdo_sqlite not available');
    }

    $repo = new AdminUserRepository(makeAuthPdo());

    $id = $repo->createWithRoleIds('user@example.com', 'User', 'hash', 'active', [1], 'en_AU');
    $user = $repo->findById($id);

    expect($user)->not->toBeNull();
    expect($user->locale)->toBe('en_AU');
});

test('createWithRoleIds accepts a null locale', function () {
    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('pdo_sqlite not available');
    }

    $repo = new AdminUserRepository(makeAuthPdo());

    $id = $repo->createWithRoleIds('user@example.com', 'User', 'hash', 'active', [1], null);

    expect($repo->findById($id)->locale)->toBeNull();
});

test('updateUser binds and persists locale', function () {
    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('pdo_sqlite not available');
    }

    $repo = new AdminUserRepository(makeAuthPdo());

    $id = $repo->createWithRoleIds('user@example.com', 'User', 'hash', 'active', [1], null);
    $repo->updateUser($id, 'user@example.com', 'User Updated', 'active', [1], 'en_AU');

    $user = $repo->findById($id);
    expect($user->locale)->toBe('en_AU');
    expect($user->name)->toBe('User Updated');
});

test('updateUser can clear locale to null', function () {
    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('pdo_sqlite not available');
    }

    $repo = new AdminUserRepository(makeAuthPdo());

    $id = $repo->createWithRoleIds('user@example.com', 'User', 'hash', 'active', [1], 'en_AU');
    $repo->updateUser($id, 'user@example.com', 'User', 'active', [1], null);

    expect($repo->findById($id)->locale)->toBeNull();
});
