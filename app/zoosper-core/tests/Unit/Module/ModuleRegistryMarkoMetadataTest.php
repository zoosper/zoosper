<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Module;

use Zoosper\Core\Module\ModuleRegistry;

function markoMetadataModuleFixture(): string
{
    $root = sys_get_temp_dir() . '/zoosper-marko-metadata-' . bin2hex(random_bytes(6));
    $package = $root . '/vendor/acme/content';
    mkdir($package, 0775, true);

    file_put_contents(
        $package . '/composer.json',
        json_encode([
            'name' => 'acme/content',
            'type' => 'zoosper-module',
            'extra' => ['marko' => ['module' => true]],
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT),
    );
    file_put_contents(
        $package . '/module.php',
        "<?php return ['name' => 'acme-content', 'enabled' => true];",
    );

    return $root;
}

test('vendor Zoosper package is discovered from canonical Marko metadata', function (): void {
    $modules = (new ModuleRegistry(markoMetadataModuleFixture()))->discoverModulesLive();

    expect($modules)->toHaveCount(1);
    expect($modules[0]->name)->toBe('acme-content');
    expect($modules[0]->source)->toBe('vendor');
});

function genericMarkoPackageFixture(): string
{
    $root = sys_get_temp_dir() . '/zoosper-generic-marko-' . bin2hex(random_bytes(6));
    $package = $root . '/vendor/marko/example';
    mkdir($package, 0775, true);

    file_put_contents(
        $package . '/composer.json',
        json_encode([
            'name' => 'marko/example',
            'type' => 'library',
            'extra' => ['marko' => ['module' => true]],
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT),
    );
    file_put_contents($package . '/module.php', "<?php return ['name' => 'marko-example'];");

    return $root;
}

test('generic Marko framework packages are not mistaken for Zoosper CMS modules', function (): void {
    expect((new ModuleRegistry(genericMarkoPackageFixture()))->discoverModulesLive())->toBe([]);
});
