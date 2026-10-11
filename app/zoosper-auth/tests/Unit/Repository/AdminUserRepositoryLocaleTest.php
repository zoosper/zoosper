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

// Explicit fetch modes must preserve identity and permission semantics.
test('Auth repositories preserve reads with a numeric default fetch mode', function (): void {
    $pdo = makeAuthPdo();
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_NUM);
    $pdo->exec('ALTER TABLE admin_permissions ADD COLUMN parent_code TEXT');
    $pdo->exec('ALTER TABLE admin_permissions ADD COLUMN sort_order INTEGER DEFAULT 0');
    $pdo->exec("INSERT INTO admin_permissions (id, code, label, created_at) VALUES (1, 'page.edit', 'Edit', '2026-01-01')");
    $pdo->exec('INSERT INTO admin_role_permissions (role_id, permission_id) VALUES (1, 1)');
    $users = new AdminUserRepository($pdo);
    $id = $users->createWithRoleIds('read@example.test', 'Read', 'hash', 'active', [1], 'en_AU');
    expect($users->findById($id)?->email)->toBe('read@example.test')
        ->and($users->findByEmail('read@example.test')?->locale)->toBe('en_AU')
        ->and($users->findById(999))->toBeNull()
        ->and($users->findByEmail('absent@example.test'))->toBeNull()
        ->and($users->all()[0]->permissions)->toBe(['page.edit'])
        ->and($users->search('read')[0]->permissions)->toBe(['page.edit'])
        ->and($users->allForAssignment()[0]->permissions)->toBe([])
        ->and($users->findForAssignmentWithSelected([$id], 'absent')[0]->id)->toBe($id)
        ->and($users->roleIdsForUser($id))->toBe([1]);
    $roles = new \Zoosper\Auth\Repository\RoleRepository($pdo);
    $role = $roles->findRoleById(1);
    expect($role)->toBeArray()
        ->and($role['code'])->toBe('super_admin')
        ->and(array_filter(array_keys($role), 'is_int'))->toBe([])
        ->and($roles->findRoleByCode('super_admin')['code'])->toBe('super_admin')
        ->and($roles->findRoleById(999))->toBeNull()
        ->and($roles->findRoleByCode('absent'))->toBeNull()
        ->and(array_is_list($roles->allRoles()))->toBeTrue()
        ->and($roles->allRoles()[0]['code'])->toBe('super_admin')
        ->and($roles->allPermissions()[0]['code'])->toBe('page.edit')
        ->and($roles->permissionIdsForRole(1))->toBe([1])
        ->and($roles->userIdsForRole(1))->toBe([$id]);
});

test('Auth repository collections retain empty list contracts', function (): void {
    $pdo = makeAuthPdo();
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_NUM);
    $pdo->exec('DELETE FROM admin_roles');
    $users = new AdminUserRepository($pdo);
    $roles = new \Zoosper\Auth\Repository\RoleRepository($pdo);
    expect($users->all())->toBe([])
        ->and($users->search('absent'))->toBe([])
        ->and($users->allForAssignment())->toBe([])
        ->and($users->roleIdsForUser(999))->toBe([])
        ->and($roles->allRoles())->toBe([])
        ->and($roles->permissionIdsForRole(999))->toBe([])
        ->and($roles->userIdsForRole(999))->toBe([]);
});
