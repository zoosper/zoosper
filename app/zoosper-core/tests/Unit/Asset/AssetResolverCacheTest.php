<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Asset;

use Zoosper\Core\Asset\AssetModuleRegistry;
use Zoosper\Core\Asset\AssetResolver;

it('memoizes resolved assets across identical calls', function (): void {
    $tempDir = sys_get_temp_dir() . '/zoosper_asset_cache_' . bin2hex(random_bytes(4));
    mkdir($tempDir, 0777, true);
    $cssFile = $tempDir . '/styles.css';
    file_put_contents($cssFile, 'body { margin: 0; }');

    try {
        $registry = new AssetModuleRegistry(['test-mod' => $tempDir]);
        $resolver = new AssetResolver($registry);

        $first = $resolver->resolve('test-mod', 'styles.css');
        $second = $resolver->resolve('test-mod', 'styles.css');

        expect($first)->toBe($second)
            ->and($first->size)->toBe(filesize($cssFile))
            ->and($first->mimeType)->toBe('text/css');
    } finally {
        unlink($cssFile);
        rmdir($tempDir);
    }
});










