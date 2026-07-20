<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Scaffold;

use Zoosper\Core\Scaffold\PackageModuleScaffolder;

test('package module scaffolder interpolates generated package test filename', function () {
    $root = sys_get_temp_dir() . '/zoosper-package-scaffold-filename-' . bin2hex(random_bytes(4));
    mkdir($root . '/packages', 0775, true);

    (new PackageModuleScaffolder($root))->scaffold('Acme/MovieLibrary');

    expect(is_file($root . '/packages/acme-movie-library/tests/Unit/MovieLibraryPackageTest.php'))->toBeTrue();
    expect(is_file($root . '/packages/acme-movie-library/tests/Unit/{$classPrefix}PackageTest.php'))->toBeFalse();
});
