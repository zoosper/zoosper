<?php

declare(strict_types=1);

it('publishes the Media visual Grid stylesheet through its module asset root', function (): void {
    $root = dirname(__DIR__, 3);

    $assetRoots = require $root . '/config/assets.php';

    expect($assetRoots)
        ->toHaveKey('zoosper-media')
        ->and($assetRoots['zoosper-media'])
        ->toBe($root . '/resources/admin');

    $manifest = require $root . '/config/admin_assets.php';

    expect($manifest)->toHaveKey('assets');
    expect($manifest['assets'])->toHaveKey('media.visual-grid');

    $asset = $manifest['assets']['media.visual-grid'];

    expect($asset['type'])->toBe('style');
    expect($asset['path'])->toBe(
        '/asset/zoosper-media/css/media-visual-grid.css'
    );

    $stylesheet = $root . '/resources/admin/css/media-visual-grid.css';

    expect($stylesheet)->toBeFile();

    $css = (string) file_get_contents($stylesheet);

    expect($css)
        ->toContain('.media-visual-grid')
        ->toContain('.media-card img')
        ->toContain('max-width: 100%');
});
