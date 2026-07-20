<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Scaffold;

use Zoosper\Core\Scaffold\PackageModuleScaffolder;

test('package module scaffolder preserves camel case boundaries in module name and namespace', function () {
    $root = sys_get_temp_dir() . '/zoosper-package-scaffold-module-name-' . bin2hex(random_bytes(4));
    mkdir($root . '/packages', 0775, true);

    $result = (new PackageModuleScaffolder($root))->scaffold('Acme/MovieLibrary');

    expect($result->moduleName)->toBe('Acme_MovieLibrary');
    expect($result->namespace)->toBe('Acme\\MovieLibrary\\');
    expect(is_file($root . '/packages/acme-movie-library/tests/Unit/MovieLibraryPackageTest.php'))->toBeTrue();
});
