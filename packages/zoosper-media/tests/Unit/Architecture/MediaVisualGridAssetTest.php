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

    $stylesheet = $root . '/resources/admin/css/media-visual-grid.css';

    expect($stylesheet)->toBeFile();

    $version = substr(hash_file('sha256', $stylesheet) ?: 'dev', 0, 12);

    expect($asset['type'])->toBe('style');
    expect($asset['path'])->toBe(
        '/asset/zoosper-media/css/media-visual-grid.css?v=' . $version
    );

    $css = (string) file_get_contents($stylesheet);

    expect($css)
        ->toContain('.media-visual-grid')
        ->toContain('.media-card img')
        ->toContain('max-width: 100%')
        ->toContain('var(--admin-surface-muted')
        ->toContain('var(--admin-text-muted')
        ->toContain('var(--admin-shadow-sm')
        ->not->toContain('var(--admin-muted')
        ->not->toMatch('/<script|\son[a-z]+\s*=|javascript:/i');
});











