<?php

declare(strict_types=1);

use Zoosper\Admin\Asset\AdminAssetRegistry;
use Zoosper\Core\Module\ModuleRegistry;

it('loads every enabled module Admin asset manifest through the real registry', function (): void {
    $root = dirname(__DIR__, 5);
    $assets = (new AdminAssetRegistry(new ModuleRegistry($root)))->all();

    foreach ($assets as $asset) {
        expect($asset->handle)->not->toBe('')
            ->and($asset->path)->not->toBe('')
            ->and($asset->type)->toBeIn(['style', 'script']);
    }
});

it('contains no raw declarations inside canonical wrapped manifests', function (): void {
    $root = dirname(__DIR__, 5);
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile() || $file->getFilename() !== 'admin_assets.php' || basename($file->getPath()) !== 'config') { continue; }
        $manifest = (static fn (string $path): mixed => require $path)($file->getPathname());
        expect($manifest)->toBeArray();
        $declarations = array_key_exists('assets', $manifest) ? $manifest['assets'] : $manifest;
        if (!is_array($declarations)) { continue; }
        foreach ($declarations as $handle => $declaration) {
            expect($handle)->toBeString()->not->toBe('')
                ->and($declaration)->toBeArray();
        }
    }
});
