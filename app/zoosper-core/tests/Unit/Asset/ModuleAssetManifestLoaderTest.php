<?php

declare(strict_types=1);

use Zoosper\Core\Asset\AssetModuleRegistry;
use Zoosper\Core\Asset\AssetResolver;
use Zoosper\Core\Asset\ModuleAssetManifestLoader;

it('merges a valid manifest into the registry and resolves the asset', function (): void {
    $registry = new AssetModuleRegistry();

    $defs = require __DIR__ . '/fixtures/modA/config-assets.php';
    $registered = ModuleAssetManifestLoader::mergeDefinitions($registry, $defs, 'modA');

    expect($registered)->toBe(['mod-a'])
        ->and($registry->has('mod-a'))->toBeTrue();

    // The registered dir should actually resolve a real file.
    $asset = (new AssetResolver($registry))->resolve('mod-a', 'css/a.css');
    expect($asset->mimeType)->toBe('text/css')
        ->and(is_file($asset->absolutePath))->toBeTrue();
});

it('merges multiple module manifests independently', function (): void {
    $registry = new AssetModuleRegistry();

    ModuleAssetManifestLoader::mergeDefinitions(
        $registry,
        require __DIR__ . '/fixtures/modA/config-assets.php',
        'modA'
    );
    ModuleAssetManifestLoader::mergeDefinitions(
        $registry,
        require __DIR__ . '/fixtures/modB/config-assets.php',
        'modB'
    );

    expect($registry->has('mod-a'))->toBeTrue()
        ->and($registry->has('mod-b'))->toBeTrue()
        ->and($registry->all())->toHaveCount(2);
});

it('rejects a manifest that does not return an array', function (): void {
    $registry = new AssetModuleRegistry();
    $defs = require __DIR__ . '/fixtures/invalid-not-array.php';

    expect(fn () => ModuleAssetManifestLoader::mergeDefinitions($registry, $defs, 'invalid'))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects a manifest entry with a non-string directory', function (): void {
    $registry = new AssetModuleRegistry();
    $defs = require __DIR__ . '/fixtures/invalid-bad-value.php';

    expect(fn () => ModuleAssetManifestLoader::mergeDefinitions($registry, $defs, 'invalid'))
        ->toThrow(InvalidArgumentException::class);
});

it('treats an empty manifest as a no-op', function (): void {
    $registry = new AssetModuleRegistry();

    $registered = ModuleAssetManifestLoader::mergeDefinitions($registry, [], 'empty');

    expect($registered)->toBe([])
        ->and($registry->all())->toBe([]);
});
