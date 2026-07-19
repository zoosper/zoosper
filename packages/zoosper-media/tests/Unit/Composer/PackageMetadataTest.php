<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Composer;

test('zoosper media package metadata is suitable for composer discovery', function () {
    $packageRoot = dirname(__DIR__, 3);
    $composer = json_decode((string) file_get_contents($packageRoot . '/composer.json'), true);

    expect($composer['name'])->toBe('zoosper/media');
    expect($composer['type'])->toBe('zoosper-module');
    expect($composer['extra']['zoosper']['module'])->toBe('module.php');
    expect($composer['autoload']['psr-4']['Zoosper\\Media\\'])->toBe('src/');
});
