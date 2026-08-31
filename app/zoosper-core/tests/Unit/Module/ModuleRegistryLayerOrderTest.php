<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Module;

use Zoosper\Core\Module\ModuleRegistry;

function moduleLayerOrderFixture(): string
{
    $root = sys_get_temp_dir() . '/zoosper-layer-order-' . bin2hex(random_bytes(6));

    foreach ([
        ['vendor/acme/zeta', 'acme/zeta'],
        ['vendor/acme/alpha', 'acme/alpha'],
        ['modules/acme-middle', 'acme/middle'],
        ['app/acme-zeta', 'acme/zeta-app'],
        ['app/acme-alpha', 'acme/alpha-app'],
    ] as [$relativePath, $packageName]) {
        $path = $root . '/' . $relativePath;
        mkdir($path, 0775, true);
        file_put_contents(
            $path . '/composer.json',
            json_encode([
                'name' => $packageName,
                'type' => 'zoosper-module',
                'extra' => ['marko' => ['module' => true]],
            ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT),
        );
        file_put_contents($path . '/module.php', "<?php return [];\n");
    }

    return $root;
}

test('module order follows vendor then modules then app with stable names', function (): void {
    $modules = (new ModuleRegistry(moduleLayerOrderFixture()))->discoverModulesLive();

    expect(array_map(static fn ($module): string => $module->name, $modules))->toBe([
        'acme-alpha',
        'acme-zeta',
        'acme-middle',
        'acme-alpha-app',
        'acme-zeta-app',
    ]);
});










