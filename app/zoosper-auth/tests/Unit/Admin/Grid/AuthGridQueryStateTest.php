<?php

declare(strict_types=1);

use Zoosper\Auth\Admin\Grid\AuthGridQueryState;

it('normalises supported scalar Auth Grid query values', function (): void {
    expect(AuthGridQueryState::fromQuery([
        'page' => ' 2 ',
        'page_size' => 50,
        'sort' => ' email ',
        'dir' => ' asc ',
        'q' => ' admin@example.test ',
        'status' => ' active ',
        'ignored' => 'not exposed',
    ]))->toBe([
        'page' => '2',
        'page_size' => '50',
        'sort' => 'email',
        'dir' => 'asc',
        'q' => 'admin@example.test',
        'status' => 'active',
    ]);
});

it('normalises list state without accepting nested or duplicate values', function (): void {
    expect(AuthGridQueryState::fromQuery([
        'visible_columns' => ['name', ' name ', ['nested'], 'email', 'email', null],
        'column_order' => ['id', 'name', new stdClass(), 'actions'],
    ]))->toBe([
        'visible_columns' => ['name', 'email'],
        'column_order' => ['id', 'name', 'actions'],
    ]);
});

it('accepts only positive decimal bookmark identifiers', function (): void {
    expect(AuthGridQueryState::bookmarkId(['bookmark_id' => '12']))->toBe(12)
        ->and(AuthGridQueryState::bookmarkId(['bookmark_id' => 0]))->toBeNull()
        ->and(AuthGridQueryState::bookmarkId(['bookmark_id' => '-1']))->toBeNull()
        ->and(AuthGridQueryState::bookmarkId(['bookmark_id' => '1 OR 1=1']))->toBeNull()
        ->and(AuthGridQueryState::bookmarkId(['bookmark_id' => ['12']]))->toBeNull();
});
