<?php

declare(strict_types=1);

use Zoosper\Media\EditorJs\MediaPickerReadQuery;
use Zoosper\Media\EditorJs\MediaPickerReadRepository;
use Zoosper\Pagination\Pager;

it('returns only active published images with search and bounded pagination', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE media_assets (id INTEGER PRIMARY KEY, uuid TEXT, filename TEXT, original_filename TEXT, mime_type TEXT, extension TEXT, size_bytes INTEGER, storage_path TEXT, public_path TEXT NULL, status TEXT, created_by INTEGER NULL, created_at TEXT, updated_at TEXT)');
    $insert = $pdo->prepare('INSERT INTO media_assets VALUES (:id,:uuid,:filename,:original,:mime,:extension,:size,:storage,:public,:status,NULL,:created,:updated)');
    foreach ([
        [1, 'a', 'hero.webp', 'Hero Product.webp', 'image/webp', 'webp', 100, 'private/hero.webp', '/media/hero.webp', 'active'],
        [2, 'b', 'old.png', 'Hero Old.png', 'image/png', 'png', 200, 'private/old.png', '/media/old.png', 'archived'],
        [3, 'c', 'guide.pdf', 'Hero Guide.pdf', 'application/pdf', 'pdf', 300, 'private/guide.pdf', '/media/guide.pdf', 'active'],
        [4, 'd', 'hidden.jpg', 'Hero Hidden.jpg', 'image/jpeg', 'jpg', 400, 'private/hidden.jpg', null, 'active'],
    ] as [$id,$uuid,$filename,$original,$mime,$extension,$size,$storage,$public,$status]) {
        $insert->execute(['id'=>$id,'uuid'=>$uuid,'filename'=>$filename,'original'=>$original,'mime'=>$mime,'extension'=>$extension,'size'=>$size,'storage'=>$storage,'public'=>$public,'status'=>$status,'created'=>'2026-09-05 00:00:00','updated'=>'2026-09-05 00:00:00']);
    }

    $result = (new MediaPickerReadRepository($pdo))->paginate(
        new MediaPickerReadQuery(new Pager(1, 20), 'Hero')
    );

    expect($result->total)->toBe(1)
        ->and($result->items)->toHaveCount(1)
        ->and($result->items[0]->id)->toBe(1)
        ->and($result->items[0]->publicPath)->toBe('/media/hero.webp');
});
