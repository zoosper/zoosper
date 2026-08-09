<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Scaffold;

use Zoosper\Core\Scaffold\ModuleScaffolder;

test('scaffolds a module folder with core convention files', function () {
    $root = sys_get_temp_dir() . '/zoosper-scaffold-' . bin2hex(random_bytes(6));
    mkdir($root . '/app', 0775, true);

    $result = (new ModuleScaffolder($root))->scaffold('Acme_Blog');

    expect($result->moduleName)->toBe('Acme_Blog');
    expect(is_file($root . '/app/acme-blog/composer.json'))->toBeTrue();
    expect(is_file($root . '/app/acme-blog/module.php'))->toBeTrue();

    $composer = json_decode(
        (string) file_get_contents($root . '/app/acme-blog/composer.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );
    $module = require $root . '/app/acme-blog/module.php';

    expect($composer['name'])->toBe('acme/blog');
    expect($composer['type'])->toBe('zoosper-module');
    expect($composer['require']['zoosper/core'])->toBe('dev-dev');
    expect($composer['extra']['marko']['module'] ?? null)->not->toBeTrue();
    expect($module)->toBe([]);
    expect(is_file($root . '/app/acme-blog/config/events.php'))->toBeTrue();
    expect(is_file($root . '/app/acme-blog/config/db_schema.php'))->toBeTrue();
    expect(is_file($root . '/app/acme-blog/tests/Pest.php'))->toBeTrue();
});

