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
    expect($manifest['autoload']['psr-4']['Zoosper\\Media\\'])->toBe('src/');
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
