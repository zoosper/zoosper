<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Repository;

use PDO;
use Zoosper\Media\Repository\MediaAssetCriteria;
use Zoosper\Media\Repository\MediaAssetRepository;
use Zoosper\Pagination\Pager;

function mediaRepositoryPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('CREATE TABLE media_assets (id INTEGER PRIMARY KEY AUTOINCREMENT, uuid TEXT NOT NULL, filename TEXT NOT NULL, original_filename TEXT NOT NULL, mime_type TEXT NOT NULL, extension TEXT NOT NULL, size_bytes INTEGER NOT NULL, storage_path TEXT NOT NULL, public_path TEXT NULL, status TEXT NOT NULL, created_by INTEGER NULL, created_at TEXT NOT NULL, updated_at TEXT NOT NULL)');

    return $pdo;
}

test('creates and hydrates media asset records', function () {
    $repo = new MediaAssetRepository(mediaRepositoryPdo());
    $id = $repo->create('uuid-1', 'uuid-1.png', 'Original.png', 'image/png', 'png', 123, 'storage/media/original/uuid-1.png', '/media/uuid-1.png', 7);

    $asset = $repo->findById($id);

    expect($asset)->not->toBeNull();
    expect($asset->uuid)->toBe('uuid-1');
    expect($asset->publicPath)->toBe('/media/uuid-1.png');
    expect($asset->createdBy)->toBe(7);
});

test('lists and paginates hydrated media assets with bounded and filtered reads', function () {
    $repo = new MediaAssetRepository(mediaRepositoryPdo());
    $criteria = new MediaAssetCriteria(new Pager(1, 1), query: 'Original', sortBy: 'id', sortDirection: 'asc');

    expect($repo->latest())->toBe([])
        ->and($repo->paginate($criteria)->items)->toBe([]);

    $first = $repo->create('uuid-1', 'one.png', 'Original one.png', 'image/png', 'png', 123, 'storage/one.png', null);
    $second = $repo->create('uuid-2', 'two.png', 'Original two.png', 'image/png', 'png', 456, 'storage/two.png', '/media/two.png', 7);
    $repo->create('uuid-3', 'three.jpg', 'Other.jpg', 'image/jpeg', 'jpg', 789, 'storage/three.jpg', null);

    $latest = $repo->latest(2);
    expect(array_map(fn ($asset) => $asset->id, $latest))->toBe([3, $second])
        ->and($latest[1]->publicPath)->toBe('/media/two.png')
        ->and($latest[1]->createdBy)->toBe(7)
        ->and($repo->latest(0))->toHaveCount(1);

    $page = $repo->paginate($criteria);
    expect($page->total)->toBe(2)
        ->and($page->items[0]->id)->toBe($first)
        ->and($page->items[0]->publicPath)->toBeNull()
        ->and($page->items[0]->createdBy)->toBeNull();

    $lastPage = $repo->paginate(new MediaAssetCriteria(new Pager(99, 1), query: 'Original', sortBy: 'id', sortDirection: 'asc'));
    expect($lastPage->page)->toBe(2)
        ->and($lastPage->items[0]->id)->toBe($second);
});











