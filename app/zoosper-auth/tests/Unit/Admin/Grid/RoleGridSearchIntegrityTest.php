<?php

declare(strict_types=1);

use Zoosper\Auth\Admin\Grid\PdoRoleGridReadRepository;
use Zoosper\Auth\Admin\Grid\RoleGridCriteria;
use Zoosper\Auth\Admin\Grid\RoleGridSqlBuilder;
use Zoosper\Pagination\Pager;

function roleGridSearchPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->exec('CREATE TABLE admin_roles (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT NOT NULL, label TEXT NOT NULL)');
    return $pdo;
}

it('searches role labels and codes with distinct native PDO placeholders', function (): void {
    $pdo = roleGridSearchPdo();
    $pdo->exec("INSERT INTO admin_roles (code, label) VALUES ('super_admin', 'Super Administrator'), ('content_editor', 'Content Editor')");

    $result = (new PdoRoleGridReadRepository($pdo, new RoleGridSqlBuilder()))->paginate(
        new RoleGridCriteria(new Pager(1, 20), 'content', 'id', 'desc'),
    );

    expect($result->total)->toBe(1)
        ->and($result->items)->toHaveCount(1)
        ->and($result->items[0]['code'])->toBe('content_editor');
});

it('does not reuse the Roles search placeholder', function (): void {
    $source = (string) file_get_contents(dirname(__DIR__, 4) . '/src/Admin/Grid/RoleGridSqlBuilder.php');
    expect($source)
        ->toContain('r.label LIKE :grid_query_label')
        ->toContain('r.code LIKE :grid_query_code')
        ->toContain('$parameters[\'grid_query_label\'] = $query;')
        ->toContain('$parameters[\'grid_query_code\'] = $query;')
        ->not->toContain('LIKE :grid_query OR');
});
