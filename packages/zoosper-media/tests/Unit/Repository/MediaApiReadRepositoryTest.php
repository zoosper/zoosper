<?php

declare(strict_types=1);

use Zoosper\Media\Repository\MediaAssetCriteria;
use Zoosper\Media\Repository\MediaAssetRepository;
use Zoosper\Pagination\Pager;

it('paginates allow-listed Media reads with filters ordering and totals', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec(
        'CREATE TABLE media_assets ('
        . 'id INTEGER PRIMARY KEY AUTOINCREMENT, uuid TEXT NOT NULL, filename TEXT NOT NULL, '
        . 'original_filename TEXT NOT NULL, mime_type TEXT NOT NULL, extension TEXT NOT NULL, '
        . 'size_bytes INTEGER NOT NULL, storage_path TEXT NOT NULL, public_path TEXT NULL, '
        . 'status TEXT NOT NULL, created_by INTEGER NULL, created_at TEXT NOT NULL, updated_at TEXT NOT NULL)'
    );
    $insert = $pdo->prepare(
        'INSERT INTO media_assets '
        . '(uuid, filename, original_filename, mime_type, extension, size_bytes, storage_path, public_path, status, created_by, created_at, updated_at) '
        . 'VALUES (:uuid, :filename, :original_filename, :mime_type, :extension, :size_bytes, :storage_path, :public_path, :status, NULL, :created_at, :updated_at)'
    );
    foreach ([
        ['one', 'stored-one.webp', 'product-one.webp', 'image/webp', 'webp', 100, 'storage/media/original/one.webp', '/media/one.webp', 'active', '2026-08-21 01:00:00'],
        ['two', 'stored-two.webp', 'product-two.webp', 'image/webp', 'webp', 200, 'storage/media/original/two.webp', '/media/two.webp', 'active', '2026-08-21 02:00:00'],
        ['three', 'stored-three.png', 'archived.png', 'image/png', 'png', 300, 'storage/media/original/three.png', '/media/three.png', 'archived', '2026-08-21 03:00:00'],
    ] as [$uuid, $filename, $original, $mime, $extension, $size, $storage, $public, $status, $created]) {
        $insert->execute([
            'uuid' => $uuid,
            'filename' => $filename,
            'original_filename' => $original,
            'mime_type' => $mime,
            'extension' => $extension,
            'size_bytes' => $size,
            'storage_path' => $storage,
            'public_path' => $public,
            'status' => $status,
            'created_at' => $created,
            'updated_at' => $created,
        ]);
    }

    $result = (new MediaAssetRepository($pdo))->paginate(new MediaAssetCriteria(
        pager: new Pager(9, 1),
        query: 'product',
        status: 'active',
        mimeType: 'image/webp',
        extension: 'webp',
        sortBy: 'size_bytes',
        sortDirection: 'asc',
    ));

    expect($result->total)->toBe(2)
        ->and($result->page)->toBe(2)
        ->and($result->pageSize)->toBe(1)
        ->and($result->totalPages())->toBe(2)
        ->and($result->hasPrevious())->toBeTrue()
        ->and($result->hasNext())->toBeFalse()
        ->and($result->items)->toHaveCount(1)
        ->and($result->items[0]->uuid)->toBe('two');
});











