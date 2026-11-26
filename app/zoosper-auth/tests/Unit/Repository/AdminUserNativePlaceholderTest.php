<?php

declare(strict_types=1);

use Zoosper\Auth\Admin\Grid\AdminUserGridCriteria;
use Zoosper\Auth\Admin\Grid\AdminUserGridSqlBuilder;
use Zoosper\Auth\Admin\Grid\PdoAdminUserGridReadRepository;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Pagination\Pager;

final class UniquePlaceholderPdo extends PDO
{
    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        preg_match_all('/(?<!:):[a-zA-Z_][a-zA-Z0-9_]*/', $query, $matches);
        if (count($matches[0]) !== count(array_unique($matches[0]))) {
            throw new PDOException('Repeated named placeholder is incompatible with native prepared statements');
        }

        return parent::prepare($query, $options);
    }
}

function nativePlaceholderUsersPdo(): PDO
{
    $pdo = new UniquePlaceholderPdo('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE admin_users (id INTEGER PRIMARY KEY, name TEXT, email TEXT, password_hash TEXT, status TEXT, locale TEXT)');
    $pdo->exec('CREATE TABLE admin_permissions (id INTEGER PRIMARY KEY, code TEXT)');
    $pdo->exec('CREATE TABLE admin_role_permissions (role_id INTEGER, permission_id INTEGER)');
    $pdo->exec('CREATE TABLE admin_user_roles (user_id INTEGER, role_id INTEGER)');
    $pdo->exec("INSERT INTO admin_users VALUES (1, 'Alice', 'first@example.test', 'hash', 'active', NULL), (2, 'Bob', 'alice@example.test', 'hash', 'active', NULL), (3, 'Alice Disabled', 'other@example.test', 'hash', 'inactive', NULL)");

    return $pdo;
}

it('uses unique placeholders for both grid queries and matches name or email', function (): void {
    $repository = new PdoAdminUserGridReadRepository(nativePlaceholderUsersPdo(), new AdminUserGridSqlBuilder());
    $result = $repository->paginate(new AdminUserGridCriteria(new Pager(1, 10), 'alice', 'active', 'name', 'asc'));

    expect($result->total)->toBe(2)
        ->and(array_column($result->items, 'id'))->toBe([1, 2]);
});

it('uses unique placeholders in assignment search while retaining trimming and limits', function (): void {
    $repository = new AdminUserRepository(nativePlaceholderUsersPdo());

    expect(array_map(static fn ($user): int => $user->id, $repository->allForAssignment(' alice ', 2)))->toBe([1, 3])
        ->and(array_map(static fn ($user): int => $user->id, $repository->allForAssignment('alice@example')))->toBe([2]);
});

it('uses unique placeholders in user search and preserves untrimmed search terms', function (): void {
    $repository = new AdminUserRepository(nativePlaceholderUsersPdo());

    expect(array_map(static fn ($user): int => $user->id, $repository->search('alice', 2)))->toBe([1, 3])
        ->and(array_map(static fn ($user): int => $user->id, $repository->search('alice@example')))->toBe([2])
        ->and($repository->search(' alice '))->toBe([]);
});