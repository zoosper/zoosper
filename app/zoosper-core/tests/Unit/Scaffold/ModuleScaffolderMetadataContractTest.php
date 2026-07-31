<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Scaffold;

use Zoosper\Core\Scaffold\ModuleScaffolder;
use Zoosper\Core\Scaffold\PackageModuleScaffolder;

test('all module scaffolders generate Composer-owned metadata', function (): void {
    $root = sys_get_temp_dir() . '/zoosper-scaffolder-contract-' . bin2hex(random_bytes(6));
    mkdir($root . '/app', 0775, true);
    mkdir($root . '/packages', 0775, true);

    (new ModuleScaffolder($root))->scaffold('Acme_Blog');
    (new PackageModuleScaffolder($root))->scaffold('Acme/MovieLibrary');

    foreach (['app/acme-blog', 'packages/acme-movie-library'] as $relativePath) {
        $packagePath = $root . '/' . $relativePath;
        $composer = json_decode(
            (string) file_get_contents($packagePath . '/composer.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $module = require $packagePath . '/module.php';

        expect($composer['type'])->toBe('zoosper-module');
        expect($composer['extra']['marko']['module'])->toBeTrue();
        expect($composer['require']['zoosper/core'])->toBe('dev-dev');
        expect($module)->toBe([]);
    }
});
