<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Module;

use Zoosper\Core\Module\ModuleRegistry;

test('module registry discovers a Composer vendor package module from installed metadata', function () {
    $root = sys_get_temp_dir() . '/zoosper-vendor-discovery-' . bin2hex(random_bytes(4));
    $package = $root . '/vendor/acme/movie-library';
    createVendorModuleFixture(
        root: $root,
        packagePath: $package,
        packageName: 'acme/movie-library',
        moduleName: 'Acme_MovieLibrary',
    );

    $modules = (new ModuleRegistry($root))->enabledModules();
    $match = null;
    foreach ($modules as $module) {
        if (($module->name ?? null) === 'Acme_MovieLibrary') {
            $match = $module;
            break;
        }
    }

    expect($match)->not->toBeNull();
    expect($match->configPath('services.php'))->toBe($package . '/config/services.php');
});

test('vendor package discovery fixture keeps module source outside app and packages', function () {
    $root = sys_get_temp_dir() . '/zoosper-vendor-contract-' . bin2hex(random_bytes(4));
    createVendorModuleFixture(
        root: $root,
        packagePath: $root . '/vendor/acme/health-data',
        packageName: 'acme/health-data',
        moduleName: 'Acme_HealthData',
    );

    $modules = (new ModuleRegistry($root))->enabledModules();
    $names = array_map(static fn (object $module): string => (string) ($module->name ?? ''), $modules);

    expect($names)->toContain('Acme_HealthData');
});

function createVendorModuleFixture(string $root, string $packagePath, string $packageName, string $moduleName): void
{
    mkdir($packagePath . '/config', 0775, true);
    mkdir($root . '/vendor/composer', 0775, true);

    file_put_contents($packagePath . '/module.php', "<?php\n\ndeclare(strict_types=1);\n\nreturn ['name' => '{$moduleName}', 'enabled' => true, 'version' => '0.1.0', 'sort_order' => 100];\n");
    file_put_contents($packagePath . '/config/services.php', "<?php\n\ndeclare(strict_types=1);\n\nreturn [];\n");
    file_put_contents($packagePath . '/composer.json', json_encode([
        'name' => $packageName,
        'type' => 'zoosper-module',
        'autoload' => ['psr-4' => [str_replace('_', '\\', $moduleName) . '\\' => 'src/']],
        'extra' => ['zoosper' => ['module' => 'module.php', 'name' => $moduleName]],
    ], JSON_PRETTY_PRINT));

    $installPath = '../' . str_replace('/', '/', $packageName);
    $installed = [[
        'name' => $packageName,
        'type' => 'zoosper-module',
        'install_path' => $installPath,
        'extra' => ['zoosper' => ['module' => 'module.php', 'name' => $moduleName]],
    ]];

    file_put_contents($root . '/vendor/composer/installed.json', json_encode(['packages' => $installed], JSON_PRETTY_PRINT));
    file_put_contents($root . '/vendor/composer/installed.php', "<?php\nreturn ['versions' => ['{$packageName}' => ['type' => 'zoosper-module', 'install_path' => __DIR__ . '/{$installPath}', 'extra' => ['zoosper' => ['module' => 'module.php', 'name' => '{$moduleName}']]]]];\n");
}
