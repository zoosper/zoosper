<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Composer;

use Zoosper\Core\Composer\ModulePackageIdentity;

test('derives package identity from vendor underscore module names', function () {
    $identity = ModulePackageIdentity::fromName('Zoosper_Media');

    expect($identity)->not->toBeNull();
    expect($identity->packageName)->toBe('zoosper/media');
    expect($identity->namespace)->toBe('Zoosper\Media\\');
});

test('derives package identity from historical kebab module names', function () {
    $identity = ModulePackageIdentity::fromName('zoosper-two-factor');

    expect($identity)->not->toBeNull();
    expect($identity->moduleName)->toBe('Zoosper_TwoFactor');
    expect($identity->packageName)->toBe('zoosper/two-factor');
    expect($identity->namespace)->toBe('Zoosper\TwoFactor\\');
});

test('normalizes valid identities and folder fallbacks', function (string $name, string $folder, string $module, string $package, string $namespace): void {
    $identity = ModulePackageIdentity::fromName($name, $folder);

    expect($identity)->not->toBeNull()
        ->and($identity->moduleName)->toBe($module)
        ->and($identity->packageName)->toBe($package)
        ->and($identity->namespace)->toBe($namespace);
})->with([
    [' Acme_FooBar2 ', '', 'Acme_Foobar2', 'acme/foo-bar2', 'Acme\\Foobar2\\'],
    ['ACME-one-2', '', 'Acme_One2', 'acme/one-2', 'Acme\\One2\\'],
    ['legacy', 'acme-blog', 'Acme_Blog', 'acme/blog', 'Acme\\Blog\\'],
    ['acme-other', 'vendor-ignored', 'Acme_Other', 'acme/other', 'Acme\\Other\\'],
]);

test('rejects invalid package identities', function (string $name, string $folder): void {
    expect(ModulePackageIdentity::fromName($name, $folder))->toBeNull();
})->with([
    ['', 'acme-blog'], [' ', 'acme-blog'], ['acme', ''], ['1acme-blog', ''],
    ['acme--blog', ''], ['acme-', ''], ['acme_blog_more', ''], ['acme/blog', ''],
    ['legacy', 'bad_folder'],
]);

test('module names fall back to the folder only when missing or empty', function (): void {
    foreach ([[], ['name' => null], ['name' => '  ']] as $module) {
        $identity = ModulePackageIdentity::fromModule($module, 'acme-blog');
        expect($identity?->packageName)->toBe('acme/blog');
    }

    expect(ModulePackageIdentity::fromModule(['name' => 'bad--name'], 'acme-blog'))->toBeNull();
});










