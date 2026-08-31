<?php

declare(strict_types=1);

use Zoosper\Admin\Asset\AdminAssetRegistry;
use Zoosper\Core\Module\ModuleRegistry;

it('declares revision pagination inside the manifest assets collection', function (): void {
    $root = dirname(__DIR__, 5);
    $manifest = require $root . '/app/zoosper-page/config/admin_assets.php';

    expect($manifest)->toHaveKey('assets')
        ->and($manifest['assets'])->toBeArray()
        ->toHaveKey('page-revision-pagination')
        ->and($manifest['assets']['page-revision-pagination'])->toBe([
            'type' => 'script',
            'path' => '/assets/page/js/page-revision-pagination.js',
            'sort_order' => 260,
        ]);

    foreach ($manifest['assets'] as $handle => $declaration) {
        expect($handle)->toBeString()->not->toBe('')
            ->and($declaration)->toBeArray()
            ->and($declaration)->toHaveKeys(['type', 'path']);
    }
});

it('loads the Page Admin declarations through the real Admin asset registry', function (): void {
    $root = dirname(__DIR__, 5);
    $assets = (new AdminAssetRegistry(new ModuleRegistry($root)))->all();
    $matched = array_values(array_filter(
        $assets,
        static fn ($asset): bool => $asset->path === '/assets/page/js/page-revision-pagination.js',
    ));

    expect($matched)->toHaveCount(1)
        ->and($matched[0]->type)->toBe('script');
});

it('keeps the registered public script present', function (): void {
    $root = dirname(__DIR__, 5);
    expect(is_file($root . '/public/assets/page/js/page-revision-pagination.js'))->toBeTrue();
});










