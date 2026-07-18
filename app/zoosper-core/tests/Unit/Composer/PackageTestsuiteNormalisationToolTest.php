<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Composer;

test('package testsuite normaliser narrows broad package test discovery', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/tools/normalise-package-testsuites.php');

    expect($source)->toContain('packages/*/tests/Unit');
    expect($source)->toContain('packages/*/tests');
    expect($source)->toContain('str_replace');
});
