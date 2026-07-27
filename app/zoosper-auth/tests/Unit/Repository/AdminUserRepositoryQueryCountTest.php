<?php

declare(strict_types=1);

use Zoosper\Auth\Repository\AdminUserRepository;

final class CountingPdo extends PDO
{
    public int $statementCount = 0;

    #[\ReturnTypeWillChange]
    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        $this->statementCount++;
        return parent::prepare($query, $options);
    }

    #[\ReturnTypeWillChange]
    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
    {
        $this->statementCount++;
        return parent::query($query, $fetchMode, ...$fetchModeArgs);
    }
}

function makeAdminUsersPdo(): CountingPdo
{
    $pdo = new CountingPdo('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE admin_users (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL, name TEXT NOT NULL, password_hash TEXT NOT NULL, status TEXT NOT NULL DEFAULT "active", locale TEXT, last_login_at TEXT, created_at TEXT, updated_at TEXT)');
    $pdo->exec('CREATE TABLE admin_roles (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT NOT NULL)');
    $pdo->exec('CREATE TABLE admin_permissions (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT NOT NULL)');
    $pdo->exec('CREATE TABLE admin_user_roles (user_id INTEGER NOT NULL, role_id INTEGER NOT NULL)');
    $pdo->exec('CREATE TABLE admin_role_permissions (role_id INTEGER NOT NULL, permission_id INTEGER NOT NULL)');
    return $pdo;
}

function seedUsers(CountingPdo $pdo, int $count): void
{
    $pdo->exec("INSERT INTO admin_roles (id, code) VALUES (1, 'editor')");
    $pdo->exec("INSERT INTO admin_permissions (id, code) VALUES (1, 'page.edit'), (2, 'page.publish')");
    $pdo->exec('INSERT INTO admin_role_permissions (role_id, permission_id) VALUES (1,1), (1,2)');
    for ($i = 1; $i <= $count; $i++) {
        $pdo->exec("INSERT INTO admin_users (id,email,name,password_hash,status) VALUES ($i,'user$i@example.test','User $i','hash','active')");
        $pdo->exec("INSERT INTO admin_user_roles (user_id, role_id) VALUES ($i, 1)");
    }
}

it('loads permissions for all() in O(1) queries, not one per user', function (): void {
    $pdo = makeAdminUsersPdo();
    seedUsers($pdo, 25);
    $repo = new AdminUserRepository($pdo);
    $pdo->statementCount = 0;

    $users = $repo->all();

    expect($users)->toHaveCount(25)
        ->and($pdo->statementCount)->toBe(2);
    foreach ($users as $user) {
        expect($user->permissions)->toBe(['page.edit', 'page.publish']);
    }
});

it('loads permissions for search() in O(1) queries as well', function (): void {
    $pdo = makeAdminUsersPdo();
    seedUsers($pdo, 40);
    $repo = new AdminUserRepository($pdo);
    $pdo->statementCount = 0;

    $users = $repo->search('user', 100);

    expect($users)->toHaveCount(40)
        ->and($pdo->statementCount)->toBe(2);
});

it('allForAssignment() never queries permissions at all', function (): void {
    $pdo = makeAdminUsersPdo();
    seedUsers($pdo, 200);
    $repo = new AdminUserRepository($pdo);
    $pdo->statementCount = 0;

    $users = $repo->allForAssignment();

    expect($users)->toHaveCount(200)
        ->and($pdo->statementCount)->toBe(1);
    foreach ($users as $user) {
        expect($user->permissions)->toBe([]);
    }
});
