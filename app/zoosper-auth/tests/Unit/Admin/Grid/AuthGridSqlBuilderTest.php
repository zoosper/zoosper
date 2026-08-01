<?php

declare(strict_types=1);

use Zoosper\Auth\Admin\Grid\AdminUserGridCriteria;
use Zoosper\Auth\Admin\Grid\AdminUserGridSqlBuilder;
use Zoosper\Auth\Admin\Grid\RoleGridCriteria;
use Zoosper\Auth\Admin\Grid\RoleGridSqlBuilder;
use Zoosper\Core\Pagination\Pager;

it('builds bound Admin User search and status filters', function (): void {
    $plan = (new AdminUserGridSqlBuilder())->build(new AdminUserGridCriteria(
        pager: new Pager(1, 20),
        query: 'admin@example.test',
        status: 'active',
        sortBy: 'email',
        sortDir: 'asc',
    ));

    expect($plan->whereSql)->toContain(':grid_query')
        ->toContain(':grid_status')
        ->and($plan->orderSql)->toBe('u.email ASC, u.id DESC')
        ->and($plan->parameters)->toBe([
            'grid_query' => '%admin@example.test%',
            'grid_status' => 'active',
        ]);
});

it('allow-lists Admin User sorting', function (): void {
    $plan = (new AdminUserGridSqlBuilder())->build(new AdminUserGridCriteria(
        pager: new Pager(1, 20),
        query: '',
        status: '',
        sortBy: 'u.email DESC; DROP TABLE admin_users',
        sortDir: 'sideways',
    ));

    expect($plan->whereSql)->toBe('')
        ->and($plan->orderSql)->toBe('u.id DESC, u.id DESC')
        ->and($plan->parameters)->toBe([]);
});

it('builds bound Role search and allow-listed sorting', function (): void {
    $plan = (new RoleGridSqlBuilder())->build(new RoleGridCriteria(
        pager: new Pager(1, 50),
        query: 'content',
        sortBy: 'label',
        sortDir: 'asc',
    ));

    expect($plan->whereSql)->toBe('WHERE (r.label LIKE :grid_query OR r.code LIKE :grid_query)')
        ->and($plan->orderSql)->toBe('r.label ASC, r.id DESC')
        ->and($plan->parameters)->toBe(['grid_query' => '%content%']);
});

it('never interpolates submitted values into SQL fragments', function (): void {
    $hostile = "%' OR 1=1 --";
    $plan = (new RoleGridSqlBuilder())->build(new RoleGridCriteria(
        pager: new Pager(1, 20),
        query: $hostile,
        sortBy: 'code',
        sortDir: 'desc',
    ));

    expect($plan->whereSql)->not->toContain($hostile)
        ->and($plan->parameters['grid_query'])->toBe('%' . $hostile . '%');
});
