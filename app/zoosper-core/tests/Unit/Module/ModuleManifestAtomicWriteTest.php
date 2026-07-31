<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Module;

use Zoosper\Core\Module\ModuleManifestCompiler;

function atomicManifestFixture(): string
{
    $root = sys_get_temp_dir() . '/zoosper-atomic-manifest-' . bin2hex(random_bytes(6));
    $modulePath = $root . '/app/acme-blog';

    mkdir($modulePath, 0775, true);
    file_put_contents(
        $modulePath . '/composer.json',
        json_encode([
            'name' => 'acme/blog',
            'type' => 'zoosper-module',
            'extra' => ['marko' => ['module' => true]],
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT),
    );
    file_put_contents($modulePath . '/module.php', "<?php\nreturn [];\n");

    return $root;
}

test('compile atomically replaces a previous manifest with valid PHP', function (): void {
    $root = atomicManifestFixture();
    $cachePath = $root . '/var/cache/modules.php';
    mkdir(dirname($cachePath), 0775, true);
    file_put_contents($cachePath, "<?php\nreturn ['previous-release'];\n");

    $modules = (new ModuleManifestCompiler($root, $cachePath))->compile();
    $compiled = require $cachePath;

    expect($modules)->toHaveCount(1);
    expect($compiled)->toBeArray();
    expect($compiled[0]['name'] ?? null)->toBe('acme-blog');
    expect(glob(dirname($cachePath) . '/.modules-*') ?: [])->toBe([]);
});

test('compile leaves no temporary files after repeated replacement', function (): void {
    $root = atomicManifestFixture();
    $cachePath = $root . '/var/cache/modules.php';
    $compiler = new ModuleManifestCompiler($root, $cachePath);

    $compiler->compile();
    $firstHash = hash_file('sha256', $cachePath);
    $compiler->compile();
    $secondHash = hash_file('sha256', $cachePath);

    expect($firstHash)->toBe($secondHash);
    expect(glob(dirname($cachePath) . '/.modules-*') ?: [])->toBe([]);
});

test('compiler source does not write directly onto the live manifest path', function (): void {
    $basePath = dirname(__DIR__, 5);
    $source = (string) file_get_contents(
        $basePath . '/app/zoosper-core/src/Module/ModuleManifestCompiler.php',
    );

    expect($source)->toContain('tempnam(');
    expect($source)->toContain('rename($temporaryPath, $this->cachePath)');
    expect($source)->toContain('file_put_contents($temporaryPath, $contents, LOCK_EX)');
    expect($source)->not->toContain(
        'file_put_contents($this->cachePath, $this->renderCacheFile($modules))',
    );
});
