<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Composer;

use Zoosper\Core\Composer\ModuleComposerManifestGenerator;
use Zoosper\Core\Composer\ModulePackageIdentity;

test('generates package manifest for a module identity', function () {
    $root = sys_get_temp_dir() . '/zoosper-manifest-' . bin2hex(random_bytes(4));
    mkdir($root . '/app/zoosper-media/src', 0775, true);

    $identity = ModulePackageIdentity::fromName('Zoosper_Media');
    $manifest = (new ModuleComposerManifestGenerator($root))->manifest($identity, $root . '/app/zoosper-media');

    expect($manifest['name'])->toBe('zoosper/media');
    expect($manifest['type'])->toBe('zoosper-module');
    expect($manifest['autoload']['psr-4']['Zoosper\Media\\'])->toBe('src/');
    expect($manifest['require'])->toHaveKey('zoosper/core');
});

test('generated core manifest requires ext-pdo', function () {
    $root = sys_get_temp_dir() . '/zoosper-manifest-' . bin2hex(random_bytes(4));
    mkdir($root . '/app/zoosper-core/src', 0775, true);

    $identity = ModulePackageIdentity::fromName('zoosper-core');
    $manifest = (new ModuleComposerManifestGenerator($root))->manifest($identity, $root . '/app/zoosper-core');

    expect($manifest['name'])->toBe('zoosper/core');
    expect($manifest['require']['ext-pdo'])->toBe('*');
});

test('module file discovery tolerates absent app and modules directories', function () {
    $root = sys_get_temp_dir() . '/zoosper-manifest-empty-' . bin2hex(random_bytes(4));
    mkdir($root, 0775, true);

    $identity = ModulePackageIdentity::fromName('zoosper-core');
    $manifest = (new ModuleComposerManifestGenerator($root))->manifest($identity, $root . '/app/zoosper-core');

    expect($manifest['name'])->toBe('zoosper/core')
        ->and($manifest['require']['ext-pdo'])->toBe('*');
});

test('generate writes formatted json and preserves existing manifests unless overwrite is requested', function (): void {
    $root = sys_get_temp_dir() . '/zoosper-manifest-write-' . bin2hex(random_bytes(4));
    $module = $root . '/app/acme-module';
    mkdir($module . '/src', 0775, true);
    file_put_contents($module . '/module.php', "<?php return ['name' => 'Acme_Module'];");
    $generator = new ModuleComposerManifestGenerator($root);

    expect($generator->generate())->toBe(['app/acme-module/composer.json']);
    $content = file_get_contents($module . '/composer.json');
    expect($content)->toBe(json_encode($generator->manifest(ModulePackageIdentity::fromName('Acme_Module'), $module), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL)
        ->and($generator->generate())->toBe([]);

    file_put_contents($module . '/composer.json', 'existing');
    expect($generator->generate())->toBe([])
        ->and(file_get_contents($module . '/composer.json'))->toBe('existing')
        ->and($generator->generate(true))->toBe(['app/acme-module/composer.json'])
        ->and(file_get_contents($module . '/composer.json'))->toBe($content);
});
