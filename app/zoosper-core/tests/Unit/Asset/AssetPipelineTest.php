<?php

declare(strict_types=1);

use Zoosper\Core\Asset\AssetController;
use Zoosper\Core\Asset\AssetModuleRegistry;
use Zoosper\Core\Asset\AssetNotFoundException;
use Zoosper\Core\Asset\AssetResolver;
use Zoosper\Core\Asset\AssetUrlGenerator;

function assetFixtureBase(): string
{
    return __DIR__ . '/fixtures/mod';
}

function makeResolver(): AssetResolver
{
    $registry = new AssetModuleRegistry([
        'demo-module' => assetFixtureBase(),
    ]);

    return new AssetResolver($registry);
}

it('resolves a real module asset with correct mime and etag', function (): void {
    $asset = makeResolver()->resolve('demo-module', 'css/sample.css');

    expect($asset->mimeType)->toBe('text/css')
        ->and($asset->size)->toBeGreaterThan(0)
        ->and($asset->etag)->toMatch('/^"[a-f0-9]{32}"$/')
        ->and(is_file($asset->absolutePath))->toBeTrue();
});

it('serves a known asset via the controller with cache headers', function (): void {
    $controller = new AssetController(makeResolver());

    $response = $controller->serve('demo-module', 'js/app.js');

    expect($response['status'])->toBe(200)
        ->and($response['headers']['Content-Type'])->toBe('text/javascript')
        ->and($response['headers']['Cache-Control'])->toContain('immutable')
        ->and($response['headers']['X-Content-Type-Options'])->toBe('nosniff')
        ->and($response['body'])->toContain('console.log');
});

it('returns 304 when If-None-Match matches the etag', function (): void {
    $resolver = makeResolver();
    $asset = $resolver->resolve('demo-module', 'css/sample.css');
    $controller = new AssetController($resolver);

    $response = $controller->serve('demo-module', 'css/sample.css', [
        'If-None-Match' => $asset->etag,
    ]);

    expect($response['status'])->toBe(304)
        ->and($response['body'])->toBe('');
});

it('blocks path traversal outside the module base', function (): void {
    expect(fn () => makeResolver()->resolve('demo-module', '../secret/passwords.txt'))
        ->toThrow(AssetNotFoundException::class);
});

it('rejects null bytes and encoded traversal variants', function (): void {
    foreach (["css/sample.css\0.png", '%2e%2e/secret.css', '..%2fsecret.css', '%252e%252e/secret.css'] as $path) {
        expect(fn () => makeResolver()->resolve('demo-module', $path))
            ->toThrow(AssetNotFoundException::class);
    }
});

it('rejects symlink escapes when symlinks are available', function (): void {
    if (!function_exists('symlink')) {
        $this->markTestSkipped('Symlinks are unavailable.');
    }

    $base = assetFixtureBase();
    $outside = sys_get_temp_dir() . '/zoosper-asset-outside-' . bin2hex(random_bytes(4)) . '.css';
    $link = $base . '/css/escape-' . bin2hex(random_bytes(4)) . '.css';
    file_put_contents($outside, 'secret');
    if (!@symlink($outside, $link)) {
        @unlink($outside);
        $this->markTestSkipped('Symlink creation is not permitted.');
    }

    try {
        expect(fn () => makeResolver()->resolve('demo-module', basename($link)))
            ->toThrow(AssetNotFoundException::class);
    } finally {
        @unlink($link);
        @unlink($outside);
    }
});

it('rejects unknown modules', function (): void {
    expect(fn () => makeResolver()->resolve('nope', 'css/sample.css'))
        ->toThrow(AssetNotFoundException::class);
});

it('rejects unsupported extensions', function (): void {
    expect(fn () => makeResolver()->resolve('demo-module', 'css/sample.exe'))
        ->toThrow(AssetNotFoundException::class);
});

it('returns 404 through the controller for missing assets', function (): void {
    $response = (new AssetController(makeResolver()))->serve('demo-module', 'css/missing.css');

    expect($response['status'])->toBe(404)
        ->and($response['filePath'])->toBeNull();
});

it('generates logical asset urls', function (): void {
    $url = (new AssetUrlGenerator())->url('zoosper-admin', 'css/page-momentum.css');

    expect($url)->toBe('/asset/zoosper-admin/css/page-momentum.css');
});

it('encodes url path segments safely', function (): void {
    $url = (new AssetUrlGenerator('/asset'))->url('mod', 'css/with space.css');

    expect($url)->toBe('/asset/mod/css/with%20space.css');
});










