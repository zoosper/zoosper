<?php

declare(strict_types=1);

namespace Zoosper\Admin\Tests\Unit\Grid;

use PDO;
use Zoosper\Admin\Grid\GridBookmarkRepository;

function bookmarkDatabase(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $migration = require dirname(__DIR__, 5)
        . '/app/zoosper-admin/database/migrations/202607310002_create_admin_grid_bookmarks.php';
    $migration($pdo, 'sqlite');

    return $pdo;
}

test('grid bookmarks are isolated by admin user and grid key', function (): void {
    $repository = new GridBookmarkRepository(bookmarkDatabase());
    $repository->save(10, 'admin.pages', 'My pages', ['filters' => ['status' => 'draft']]);
    $repository->save(11, 'admin.pages', 'Other user', ['filters' => []]);
    $repository->save(10, 'admin.audit', 'Audit view', ['sort' => 'created_at']);

    expect($repository->allForUser(10, 'admin.pages'))->toHaveCount(1);
    expect($repository->allForUser(10, 'admin.pages')[0]['name'])->toBe('My pages');
    expect($repository->allForUser(10, 'admin.audit'))->toHaveCount(1);
    expect($repository->allForUser(11, 'admin.pages'))->toHaveCount(1);
});

test('saving a default bookmark clears the previous default for that user and grid', function (): void {
    $repository = new GridBookmarkRepository(bookmarkDatabase());
    $repository->save(10, 'admin.pages', 'Drafts', ['filters' => []], true);
    $repository->save(10, 'admin.pages', 'Published', ['filters' => []], true);

    $bookmarks = $repository->allForUser(10, 'admin.pages');
    $defaults = array_values(array_filter($bookmarks, static fn (array $row): bool => $row['is_default']));

    expect($defaults)->toHaveCount(1);
    expect($defaults[0]['name'])->toBe('Published');
});

test('deleting a bookmark cannot delete another users bookmark', function (): void {
    $repository = new GridBookmarkRepository(bookmarkDatabase());
    $repository->save(10, 'admin.pages', 'Mine', []);
    $bookmark = $repository->allForUser(10, 'admin.pages')[0];

    $repository->delete(11, 'admin.pages', $bookmark['id']);
    expect($repository->allForUser(10, 'admin.pages'))->toHaveCount(1);

    $repository->delete(10, 'admin.pages', $bookmark['id']);
    expect($repository->allForUser(10, 'admin.pages'))->toBe([]);
});
