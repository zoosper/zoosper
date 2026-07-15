<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Scaffold;

use Zoosper\Core\Scaffold\ModuleScaffolder;

test('scaffolds a module folder with core convention files', function () {
    $root = sys_get_temp_dir() . '/zoosper-scaffold-' . bin2hex(random_bytes(6));
    mkdir($root . '/app', 0775, true);

    $result = (new ModuleScaffolder($root))->scaffold('Acme_Blog');

    expect($result->moduleName)->toBe('Acme_Blog');
    expect(is_file($root . '/app/acme-blog/module.php'))->toBeTrue();
    expect(is_file($root . '/app/acme-blog/config/events.php'))->toBeTrue();
    expect(is_file($root . '/app/acme-blog/config/db_schema.php'))->toBeTrue();
    expect(is_file($root . '/app/acme-blog/tests/Pest.php'))->toBeTrue();
});
