<?php

declare(strict_types=1);

use Zoosper\Auth\Admin\Grid\AdminUserGridCriteria;
use Zoosper\Auth\Admin\Grid\AdminUserGridSqlBuilder;
use Zoosper\Auth\Admin\Grid\PdoAdminUserGridReadRepository;
use Zoosper\Auth\Admin\Grid\PdoRoleGridReadRepository;
use Zoosper\Auth\Admin\Grid\RoleGridCriteria;
use Zoosper\Auth\Admin\Grid\RoleGridSqlBuilder;
use Zoosper\Pagination\Pager;

function authGridPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE admin_users (id INTEGER PRIMARY KEY, name TEXT NOT NULL, email TEXT NOT NULL, status TEXT NOT NULL)');
    $pdo->exec('CREATE TABLE admin_roles (id INTEGER PRIMARY KEY, label TEXT NOT NULL, code TEXT NOT NULL)');
    $pdo->exec("INSERT INTO admin_users VALUES (1, 'Damu', 'damu@example.test', 'active'), (2, 'Disabled', 'disabled@example.test', 'inactive')");
    $pdo->exec("INSERT INTO admin_roles VALUES (1, 'Super Admin', 'super_admin'), (2, 'Content Admin', 'content_admin')");

    return $pdo;
}

it('paginates projected Admin User rows without sensitive columns', function (): void {
    $result = (new PdoAdminUserGridReadRepository(authGridPdo(), new AdminUserGridSqlBuilder()))->paginate(
        new AdminUserGridCriteria(new Pager(1, 20), 'damu', 'active', 'email', 'asc'),
    );

    expect($result->total)->toBe(1)
        ->and($result->items)->toBe([[ 'id' => 1, 'name' => 'Damu', 'email' => 'damu@example.test', 'status' => 'active' ]]);
});

it('paginates projected Role rows', function (): void {
    $result = (new PdoRoleGridReadRepository(authGridPdo(), new RoleGridSqlBuilder()))->paginate(
        new RoleGridCriteria(new Pager(1, 20), 'content', 'label', 'asc'),
    );

    expect($result->total)->toBe(1)
        ->and($result->items)->toBe([[ 'id' => 2, 'label' => 'Content Admin', 'code' => 'content_admin' ]]);
});
