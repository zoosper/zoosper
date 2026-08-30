<?php

declare(strict_types=1);

use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Repository\AdminUserRepository;

function authAssignmentPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE admin_users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        password_hash TEXT NOT NULL DEFAULT "",
        status TEXT NOT NULL DEFAULT "active",
        locale TEXT DEFAULT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');
    $pdo->exec('CREATE TABLE admin_roles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        label TEXT NOT NULL,
        code TEXT NOT NULL
    )');
    $pdo->exec('CREATE TABLE admin_user_roles (
        user_id INTEGER NOT NULL,
        role_id INTEGER NOT NULL,
        PRIMARY KEY (user_id, role_id)
    )');

    $pdo->exec("INSERT INTO admin_users (id, name, email) VALUES
        (1, 'Alice Admin', 'alice@example.test'),
        (2, 'Bob Editor', 'bob@example.test'),
        (3, 'Charlie Support', 'charlie@example.test'),
        (4, 'Diana Manager', 'diana@example.test')");

    return $pdo;
}

it('returns all admin users ordered by name for assignment', function (): void {
    $repo = new AdminUserRepository(authAssignmentPdo());
    $users = $repo->allForAssignment();

    expect($users)->toHaveCount(4)
        ->and($users[0]->name)->toBe('Alice Admin')
        ->and($users[1]->name)->toBe('Bob Editor')
        ->and($users[2]->name)->toBe('Charlie Support')
        ->and($users[3]->name)->toBe('Diana Manager');
});

it('filters admin users by name or email with prepared statements', function (): void {
    $repo = new AdminUserRepository(authAssignmentPdo());

    $byName = $repo->allForAssignment('bob');
    expect($byName)->toHaveCount(1)
        ->and($byName[0]->name)->toBe('Bob Editor');

    $byEmail = $repo->allForAssignment('diana@example');
    expect($byEmail)->toHaveCount(1)
        ->and($byEmail[0]->name)->toBe('Diana Manager');

    $empty = $repo->allForAssignment('nonexistent');
    expect($empty)->toHaveCount(0);
});

it('always preserves selected users even when search excludes them', function (): void {
    $repo = new AdminUserRepository(authAssignmentPdo());

    // Search for 'alice', but selected IDs include 2 (Bob) and 4 (Diana)
    $users = $repo->findForAssignmentWithSelected([2, 4], 'alice');

    $names = array_map(static fn (AdminUser $u): string => $u->name, $users);
    expect($names)->toContain('Alice Admin')
        ->toContain('Bob Editor')
        ->toContain('Diana Manager')
        ->not->toContain('Charlie Support')
        ->and($users)->toHaveCount(3);
});

it('declares assigned-user runtime and style assets with defer semantics', function (): void {
    $assets = require dirname(__DIR__, 2) . '/config/admin_assets.php';

    expect($assets)->toHaveKeys(['zoosper-admin-role-user-assignment-style', 'zoosper-admin-role-user-assignment-runtime'])
        ->and($assets['zoosper-admin-role-user-assignment-style']['screens'])->toBe(['admin-roles'])
        ->and($assets['zoosper-admin-role-user-assignment-runtime']['screens'])->toBe(['admin-roles'])
        ->and($assets['zoosper-admin-role-user-assignment-runtime']['attributes']['defer'])->toBeTrue();

    $js = (string) file_get_contents(dirname(__DIR__, 2) . '/resources/assets/admin/js/user-assignment.js');
    $css = (string) file_get_contents(dirname(__DIR__, 2) . '/resources/assets/admin/css/user-assignment.css');

    expect($js)->toContain('data-role-user-assignment')
        ->toContain('data-role-user-search')
        ->toContain('data-role-user-count')
        ->toContain('Escape')
        ->not->toContain('innerHTML')
        ->not->toContain('fetch(')
        ->and($css)->toContain('.admin-role-user-assignment')
        ->toContain('.admin-role-user-toolbar')
        ->toContain('.admin-role-user-search');
});
