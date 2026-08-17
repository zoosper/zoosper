<?php

declare(strict_types=1);

use Zoosper\Media\Lifecycle\MediaLifecycleCoordinator;
use Zoosper\Media\Lifecycle\MediaReferenceInspector;
use Zoosper\Media\Model\MediaAsset;
use Zoosper\Media\Repository\MediaAssetRepository;
use Zoosper\Media\Service\MediaStoredFileCleanupService;

function mediaReferenceDb(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE media_assets (id INTEGER PRIMARY KEY, uuid TEXT, filename TEXT, original_filename TEXT, mime_type TEXT, extension TEXT, size_bytes INTEGER, storage_path TEXT, public_path TEXT, status TEXT, created_by INTEGER NULL, created_at TEXT, updated_at TEXT)');
    $pdo->exec('CREATE TABLE pages (id INTEGER PRIMARY KEY, content TEXT, content_json TEXT NULL)');
    $pdo->exec('CREATE TABLE page_revisions (id INTEGER PRIMARY KEY, content TEXT, content_json TEXT NULL)');
    return $pdo;
}

function mediaReferenceAsset(): MediaAsset
{
    return new MediaAsset(7, 'uuid', 'asset.png', 'asset.png', 'image/png', 'png', 10, 'storage/media/original/asset.png', '/media/2026/08/asset.png', 'archived');
}

it('counts canonical Media paths in current Pages and restorable revisions', function (): void {
    $pdo = mediaReferenceDb();
    $pdo->exec("INSERT INTO pages VALUES (1, '<img src=\"/media/2026/08/asset.png\">', NULL)");
    $pdo->exec("INSERT INTO page_revisions VALUES (1, '', '{\"blocks\":[{\"type\":\"image\",\"data\":{\"file\":{\"url\":\"/media/2026/08/asset.png\"}}}]}')");
    expect((new MediaReferenceInspector($pdo))->counts(mediaReferenceAsset()))->toBe(['pages' => 1, 'page_revisions' => 1]);
});

it('blocks referenced deletion and permits archived unreferenced deletion', function (): void {
    $pdo = mediaReferenceDb();
    $asset = mediaReferenceAsset();
    $pdo->prepare('INSERT INTO media_assets VALUES (:id,:uuid,:filename,:original,:mime,:extension,:size,:storage,:public,:status,NULL,:created,:updated)')->execute(['id'=>$asset->id,'uuid'=>$asset->uuid,'filename'=>$asset->filename,'original'=>$asset->originalFilename,'mime'=>$asset->mimeType,'extension'=>$asset->extension,'size'=>$asset->sizeBytes,'storage'=>$asset->storagePath,'public'=>$asset->publicPath,'status'=>$asset->status,'created'=>'2026-01-01','updated'=>'2026-01-01']);
    $pdo->exec("INSERT INTO pages VALUES (1, '', '{\"file\":{\"url\":\"/media/2026/08/asset.png\"}}')");
    $repository = new MediaAssetRepository($pdo);
    $lifecycle = new MediaLifecycleCoordinator($pdo, $repository, new MediaStoredFileCleanupService(sys_get_temp_dir()), references: new MediaReferenceInspector($pdo));
    $blocked = $lifecycle->deletePermanentlyGuarded($asset, 1, 'admin@example.test');
    expect($blocked->successful)->toBeFalse()->and($blocked->blockers['pages'])->toBe(1)->and($repository->findById(7))->not->toBeNull();
    $pdo->exec('DELETE FROM pages');
    $deleted = $lifecycle->deletePermanentlyGuarded($asset, 1, 'admin@example.test');
    expect($deleted->successful)->toBeTrue()->and($repository->findById(7))->toBeNull();
});
