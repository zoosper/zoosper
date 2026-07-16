<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Config;

use Zoosper\Core\Config\ModuleConfigAggregator;

test('root config overrides module defaults for scalar values', function () {
    $result = ModuleConfigAggregator::mergeConfig(
        ['posts_per_page' => 10, 'theme' => 'default'],
        ['posts_per_page' => 25],
    );

    expect($result['posts_per_page'])->toBe(25);
    expect($result['theme'])->toBe('default');
});

test('associative arrays are merged recursively with high priority winning', function () {
    $result = ModuleConfigAggregator::mergeConfig(
        ['seo' => ['title' => 'Default', 'robots' => 'index']],
        ['seo' => ['title' => 'Custom']],
    );

    expect($result['seo']['title'])->toBe('Custom');
    expect($result['seo']['robots'])->toBe('index');
});

test('list values are replaced wholesale, not concatenated', function () {
    $result = ModuleConfigAggregator::mergeConfig(
        ['levels' => [2, 3, 4]],
        ['levels' => [5, 6]],
    );

    expect($result['levels'])->toBe([5, 6]);
});

test('fromDirectories layers module defaults under root overrides', function () {
    $root = sys_get_temp_dir() . '/zoosper-config-' . bin2hex(random_bytes(6));
    $moduleDir = $root . '/module-settings';
    $rootDir = $root . '/root-config';
    mkdir($moduleDir, 0775, true);
    mkdir($rootDir, 0775, true);

    file_put_contents($moduleDir . '/blog.php', "<?php return ['posts_per_page' => 10, 'sidebar' => true];");
    file_put_contents($rootDir . '/blog.php', "<?php return ['posts_per_page' => 25];");

    $items = ModuleConfigAggregator::fromDirectories([$moduleDir, $rootDir]);

    expect($items['blog']['posts_per_page'])->toBe(25);
    expect($items['blog']['sidebar'])->toBeTrue();
});

test('non-array config files are ignored', function () {
    $root = sys_get_temp_dir() . '/zoosper-config-' . bin2hex(random_bytes(6));
    mkdir($root, 0775, true);
    file_put_contents($root . '/bad.php', "<?php return 'not-an-array';");

    expect(ModuleConfigAggregator::fromDirectories([$root]))->toBe([]);
});