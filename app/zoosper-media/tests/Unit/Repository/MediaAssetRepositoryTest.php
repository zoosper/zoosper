<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Repository;

use PDO;
use Zoosper\Media\Repository\MediaAssetRepository;

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
