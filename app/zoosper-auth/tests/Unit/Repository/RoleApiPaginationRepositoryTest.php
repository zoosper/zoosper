<?php

declare(strict_types=1);

use Zoosper\Auth\Repository\RoleRepository;
use Zoosper\Pagination\Pager;

it('returns a bounded Role slice with batched permission and user relations', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE admin_roles (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT, label TEXT, created_at TEXT, updated_at TEXT)');
    $pdo->exec('CREATE TABLE admin_role_permissions (role_id INTEGER, permission_id INTEGER, PRIMARY KEY(role_id, permission_id))');
    $pdo->exec('CREATE TABLE admin_user_roles (user_id INTEGER, role_id INTEGER, PRIMARY KEY(user_id, role_id))');
    for ($i = 1; $i <= 25; $i++) {
        $label = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
        $pdo->exec("INSERT INTO admin_roles(code,label,created_at,updated_at) VALUES('role-$i','Role $label','2026-01-01','2026-01-01')");
    }
    $pdo->exec('INSERT INTO admin_role_permissions(role_id,permission_id) VALUES(11,2),(11,4),(12,3)');
    $pdo->exec('INSERT INTO admin_user_roles(user_id,role_id) VALUES(7,11),(8,11),(9,12)');
    $result = (new RoleRepository($pdo))->pageForApi(new Pager(2, 10));
    expect($result->total)->toBe(25)
        ->and($result->page)->toBe(2)
        ->and($result->totalPages())->toBe(3)
        ->and($result->items)->toHaveCount(10)
        ->and($result->items[0]['id'])->toBe(11)
        ->and($result->items[0]['permission_ids'])->toBe([2, 4])
        ->and($result->items[0]['user_ids'])->toBe([7, 8]);
});

it('clamps an out-of-range Role page to the last available page', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE admin_roles (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT, label TEXT, created_at TEXT, updated_at TEXT)');
    $pdo->exec('CREATE TABLE admin_role_permissions (role_id INTEGER, permission_id INTEGER, PRIMARY KEY(role_id, permission_id))');
    $pdo->exec('CREATE TABLE admin_user_roles (user_id INTEGER, role_id INTEGER, PRIMARY KEY(user_id, role_id))');
    $pdo->exec("INSERT INTO admin_roles(code,label,created_at,updated_at) VALUES('role','Role','2026-01-01','2026-01-01')");
    $result = (new RoleRepository($pdo))->pageForApi(new Pager(99999, 20));
    expect($result->page)->toBe(1)->and($result->items)->toHaveCount(1);
});
