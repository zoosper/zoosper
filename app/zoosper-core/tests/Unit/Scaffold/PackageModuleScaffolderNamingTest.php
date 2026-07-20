<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Scaffold;

use Zoosper\Core\Scaffold\PackageModuleScaffolder;

test('package module scaffolder preserves camel case boundaries in package names', function () {
    $root = sys_get_temp_dir() . '/zoosper-package-scaffold-naming-' . bin2hex(random_bytes(4));
    mkdir($root . '/packages', 0775, true);

    $result = (new PackageModuleScaffolder($root))->scaffold('Acme/MovieLibrary');

    expect($result->packageName)->toBe('acme/movie-library');
    expect($result->packagePath)->toEndWith('/packages/acme-movie-library');
});
