<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Composer;

test('root phpunit configuration includes package tests after module extraction', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/phpunit.xml');

    expect($source)->toContain('packages/*/tests');
});

test('package testsuite verifier checks media package tests directory', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/tools/verify-package-testsuites.php');

    expect($source)->toContain('packages/zoosper-media/tests');
    expect($source)->toContain('packages/*/tests configured');
});
