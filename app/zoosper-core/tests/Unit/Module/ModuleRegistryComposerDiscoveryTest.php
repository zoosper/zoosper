<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Module;

use Zoosper\Core\Module\ModuleRegistry;

function moduleDiscoveryFixture(): string
{
    $root = sys_get_temp_dir() . '/zoosper-module-discovery-' . bin2hex(random_bytes(4));
    mkdir($root . '/app/zoosper-page/config', 0775, true);
    mkdir($root . '/vendor/zoosper/media/config', 0775, true);

    file_put_contents($root . '/app/zoosper-page/module.php', "<?php\n\ndeclare(strict_types=1);\n\nreturn ['name' => 'zoosper-page', 'enabled' => true, 'sort_order' => 20];\n");
    file_put_contents($root . '/vendor/zoosper/media/module.php', "<?php\n\ndeclare(strict_types=1);\n\nreturn ['name' => 'Zoosper_Media', 'enabled' => true, 'sort_order' => 25];\n");
    file_put_contents($root . '/vendor/zoosper/media/composer.json', json_encode([
        'name' => 'zoosper/media',
        'type' => 'zoosper-module',
        'extra' => ['zoosper' => ['module' => 'module.php']],
    ], JSON_PRETTY_PRINT));

    return $root;
}

test('module registry discovers app modules and composer installed modules', function () {
    $modules = (new ModuleRegistry(moduleDiscoveryFixture()))->enabledModules();
    $names = array_map(static fn ($module): string => $module->name, $modules);

    expect($names)->toContain('zoosper-page');
    expect($names)->toContain('Zoosper_Media');
});

test('composer installed module exposes config path and source metadata', function () {
    $modules = (new ModuleRegistry(moduleDiscoveryFixture()))->enabledModules();
    $media = null;
    foreach ($modules as $module) {
        if ($module->name === 'Zoosper_Media') {
            $media = $module;
            break;
        }
    }

    expect($media)->not->toBeNull();
    expect($media->source)->toBe('vendor');
    expect($media->configPath('services.php'))->toEndWith('/vendor/zoosper/media/config/services.php');
});
