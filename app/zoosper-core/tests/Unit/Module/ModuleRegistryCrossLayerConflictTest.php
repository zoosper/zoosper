<?php

declare(strict_types=1);

use Zoosper\Core\Module\DuplicateModuleException;
use Zoosper\Core\Module\ModuleRegistry;

function crossLayerModuleFixture(string $first, string $second): string
{
    $root = sys_get_temp_dir() . '/zoosper-cross-layer-' . bin2hex(random_bytes(6));
    foreach ([$first, $second] as $relative) {
        $path = $root . '/' . $relative;
        mkdir($path, 0775, true);
        file_put_contents($path . '/module.php', "<?php return ['name' => 'acme-shared', 'enabled' => true];\n");
        if (str_starts_with($relative, 'vendor/')) {
            file_put_contents($path . '/composer.json', json_encode([
                'name' => 'acme/shared',
                'type' => 'zoosper-module',
                'extra' => ['marko' => ['module' => true]],
            ], JSON_THROW_ON_ERROR));
        }
    }
    return $root;
}

it('fails descriptively for app and vendor identity collisions', function (): void {
    expect(fn () => (new ModuleRegistry(crossLayerModuleFixture('app/acme-shared', 'vendor/acme/shared')))->discoverModulesLive())
        ->toThrow(DuplicateModuleException::class, 'would shadow');
});

it('fails descriptively for app and modules identity collisions', function (): void {
    expect(fn () => (new ModuleRegistry(crossLayerModuleFixture('app/acme-shared', 'modules/acme-shared')))->discoverModulesLive())
        ->toThrow(DuplicateModuleException::class, 'Remove the stale copy');
});

it('fails descriptively for modules and vendor identity collisions', function (): void {
    expect(fn () => (new ModuleRegistry(crossLayerModuleFixture('modules/acme-shared', 'vendor/acme/shared')))->discoverModulesLive())
        ->toThrow(DuplicateModuleException::class, 'discovery layers');
});
