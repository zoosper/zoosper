<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Scaffold;

use RuntimeException;
use Zoosper\Core\Scaffold\PackageModuleScaffolder;

test('scaffolds a package module under packages with composer metadata and tests', function () {
    $root = sys_get_temp_dir() . '/zoosper-package-scaffold-' . bin2hex(random_bytes(4));
    mkdir($root . '/packages', 0775, true);

    $result = (new PackageModuleScaffolder($root))->scaffold('Acme/MovieLibrary');

    expect($result->packageName)->toBe('acme/movie-library');
    expect($result->moduleName)->toBe('Acme_MovieLibrary');
    expect(is_file($root . '/packages/acme-movie-library/composer.json'))->toBeTrue();
    expect(is_file($root . '/packages/acme-movie-library/module.php'))->toBeTrue();
    expect(is_file($root . '/packages/acme-movie-library/config/db_schema.php'))->toBeTrue();
    expect(is_file($root . '/packages/acme-movie-library/tests/Unit/MovieLibraryPackageTest.php'))->toBeTrue();

    $composer = json_decode((string) file_get_contents($root . '/packages/acme-movie-library/composer.json'), true);
    expect($composer['name'])->toBe('acme/movie-library');
    expect($composer['type'])->toBe('zoosper-module');
    expect($composer['extra']['zoosper']['module'])->toBe('module.php');
    expect($composer['autoload']['psr-4'])->toHaveKey('Acme\\MovieLibrary\\');
});

test('rejects invalid package module names', function () {
    $root = sys_get_temp_dir() . '/zoosper-package-scaffold-' . bin2hex(random_bytes(4));
    mkdir($root . '/packages', 0775, true);

    expect(fn () => (new PackageModuleScaffolder($root))->scaffold('bad'))
        ->toThrow(RuntimeException::class);
});

test('bin zoosper exposes package module scaffolding command', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/bin/zoosper');

    expect($source)->toContain('make:package-module');
    expect($source)->toContain(PackageModuleScaffolder::class);
});
