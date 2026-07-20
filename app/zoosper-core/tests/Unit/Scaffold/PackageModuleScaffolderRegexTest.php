<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Scaffold;

use Zoosper\Core\Scaffold\PackageModuleScaffolder;

test('package module scaffolder accepts slash underscore and dash separators', function (string $name, string $expectedPackage) {
    $root = sys_get_temp_dir() . '/zoosper-package-scaffold-regex-' . bin2hex(random_bytes(4));
    mkdir($root . '/packages', 0775, true);

    $result = (new PackageModuleScaffolder($root))->scaffold($name);

    expect($result->packageName)->toBe($expectedPackage);
})->with([
    ['Acme/MovieLibrary', 'acme/movie-library'],
    ['Acme_MovieLibrary', 'acme/movie-library'],
    ['acme-movie-library', 'acme/movie-library'],
]);
