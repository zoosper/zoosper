<?php

declare(strict_types=1);

use Zoosper\Media\Lifecycle\MediaLifecycleCoordinator;
use Zoosper\Media\Lifecycle\MediaReferenceInspector;
use Zoosper\Media\Model\MediaAsset;
use Zoosper\Media\Repository\MediaAssetRepository;
use Zoosper\Media\Repository\MediaDerivativeRepository;
use Zoosper\Media\Service\MediaStoredFileCleanupService;

function phase10apcDb(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('CREATE TABLE media_assets (id INTEGER PRIMARY KEY, uuid TEXT, filename TEXT, original_filename TEXT, mime_type TEXT, extension TEXT, size_bytes INTEGER, storage_path TEXT, public_path TEXT, status TEXT, created_by INTEGER NULL, created_at TEXT, updated_at TEXT)');
    $pdo->exec('CREATE TABLE media_derivatives (id INTEGER PRIMARY KEY, media_asset_id INTEGER NOT NULL, profile TEXT, format TEXT, width INTEGER, height INTEGER, size_bytes INTEGER, storage_path TEXT, public_path TEXT, created_at TEXT, updated_at TEXT, FOREIGN KEY(media_asset_id) REFERENCES media_assets(id) ON DELETE CASCADE)');
    $pdo->exec('CREATE TABLE pages (id INTEGER PRIMARY KEY, content TEXT, content_json TEXT NULL)');
    $pdo->exec('CREATE TABLE page_revisions (id INTEGER PRIMARY KEY, content TEXT, content_json TEXT NULL)');
    return $pdo;
}

function phase10apcAsset(): MediaAsset
{
    return new MediaAsset(7, 'uuid', 'asset.png', 'asset.png', 'image/png', 'png', 10, 'storage/media/original/2026/08/asset.png', '/media/2026/08/asset.png', 'archived');
}

function phase10apcRemoveTree(string $root): void
{
    if (!is_dir($root)) {
        return;
    }

    $entries = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );
    foreach ($entries as $entry) {
        $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
    }
    rmdir($root);
}

function phase10apcInsert(PDO $pdo, MediaAsset $asset): void
{
    $pdo->prepare('INSERT INTO media_assets VALUES (:id,:uuid,:filename,:original,:mime,:extension,:size,:storage,:public,:status,NULL,:created,:updated)')->execute(['id'=>$asset->id,'uuid'=>$asset->uuid,'filename'=>$asset->filename,'original'=>$asset->originalFilename,'mime'=>$asset->mimeType,'extension'=>$asset->extension,'size'=>$asset->sizeBytes,'storage'=>$asset->storagePath,'public'=>$asset->publicPath,'status'=>$asset->status,'created'=>'2026-01-01','updated'=>'2026-01-01']);
}

it('fails closed when Page reference storage is partial or incompatible', function (): void {
    $asset = phase10apcAsset();
    $partial = new PDO('sqlite::memory:');
    $partial->exec('CREATE TABLE pages (id INTEGER PRIMARY KEY, content TEXT)');
    expect(fn () => (new MediaReferenceInspector($partial))->counts($asset))->toThrow(RuntimeException::class, 'Page reference storage is incomplete');
    $incompatible = new PDO('sqlite::memory:');
    $incompatible->exec('CREATE TABLE pages (id INTEGER PRIMARY KEY, content TEXT)');
    $incompatible->exec('CREATE TABLE page_revisions (id INTEGER PRIMARY KEY, snapshot TEXT)');
    expect(fn () => (new MediaReferenceInspector($incompatible))->counts($asset))->toThrow(RuntimeException::class, 'no supported content column');
});

it('removes archived unreferenced metadata and all owned files', function (): void {
    $root = sys_get_temp_dir() . '/zoosper-media-10apc-' . bin2hex(random_bytes(4));
    try {
        foreach (['storage/media/original/2026/08', 'storage/media/derivatives/2026/08', 'public/media/2026/08'] as $dir) mkdir($root . '/' . $dir, 0775, true);
        $paths = ['storage/media/original/2026/08/asset.png', 'storage/media/derivatives/2026/08/asset-thumb.webp', 'public/media/2026/08/asset.png', 'public/media/2026/08/asset-thumb.webp'];
        foreach ($paths as $path) file_put_contents($root . '/' . $path, 'media');
        $pdo = phase10apcDb(); $asset = phase10apcAsset(); phase10apcInsert($pdo, $asset);
        $pdo->exec("INSERT INTO media_derivatives VALUES (1,7,'thumb','webp',10,10,5,'storage/media/derivatives/2026/08/asset-thumb.webp','/media/2026/08/asset-thumb.webp','2026-01-01','2026-01-01')");
        $assets = new MediaAssetRepository($pdo); $derivatives = new MediaDerivativeRepository($pdo, $root);
        $lifecycle = new MediaLifecycleCoordinator($pdo, $assets, new MediaStoredFileCleanupService($root), $derivatives, new MediaReferenceInspector($pdo));
        expect($lifecycle->deletePermanentlyGuarded($asset, 1, 'admin@example.test')->successful)->toBeTrue()
            ->and($assets->findById(7))->toBeNull()->and($derivatives->forAsset(7))->toBe([]);
        foreach ($paths as $path) expect(is_file($root . '/' . $path))->toBeFalse();
    } finally {
        phase10apcRemoveTree($root);
    }
});

it('rolls metadata deletion back and preserves files when deletion fails', function (): void {
    $root = sys_get_temp_dir() . '/zoosper-media-10apc-' . bin2hex(random_bytes(4));
    try {
        mkdir($root . '/storage/media/original/2026/08', 0775, true); file_put_contents($root . '/storage/media/original/2026/08/asset.png', 'media');
        $pdo = phase10apcDb(); $asset = phase10apcAsset(); phase10apcInsert($pdo, $asset); $pdo->exec("CREATE TRIGGER deny_media_delete BEFORE DELETE ON media_assets BEGIN SELECT RAISE(ABORT, 'denied'); END");
        $assets = new MediaAssetRepository($pdo); $lifecycle = new MediaLifecycleCoordinator($pdo, $assets, new MediaStoredFileCleanupService($root), new MediaDerivativeRepository($pdo, $root), new MediaReferenceInspector($pdo));
        expect(fn () => $lifecycle->deletePermanentlyGuarded($asset, 1, 'admin@example.test'))->toThrow(RuntimeException::class, 'rolled back')
            ->and($assets->findById(7))->not->toBeNull()->and(is_file($root . '/storage/media/original/2026/08/asset.png'))->toBeTrue();
    } finally {
        phase10apcRemoveTree($root);
    }
});

it('wires guarded Admin feedback and mandatory runtime inspectors', function (): void {
    $root = dirname(__DIR__, 3); $controller = file_get_contents($root . '/src/Controller/MediaAdminController.php'); $controllers = file_get_contents($root . '/config/controllers.php'); $services = file_get_contents($root . '/config/services.php'); $coordinator = file_get_contents($root . '/src/Lifecycle/MediaLifecycleCoordinator.php');
    expect($controller)->toContain('deletePermanentlyGuarded')->toContain('FlashMessageStoreInterface')->toContain('media.lifecycle.blocked')
        ->and($controllers)->toContain('flash: $services->has(FlashMessageStoreInterface::class)')
        ->and($services)->toContain('derivatives: $services->get(MediaDerivativeRepository::class)')->toContain('references: $services->get(MediaReferenceInspector::class)')
        ->and($coordinator)->not->toContain('$this->references?->')->not->toContain('$this->derivatives?->');
});
