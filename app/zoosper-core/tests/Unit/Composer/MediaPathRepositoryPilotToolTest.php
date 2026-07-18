<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Composer;

test('media path repository pilot tool documents conservative symlink strategy', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/tools/pilot-extract-media-path-repository.php');

    expect($source)->toContain('packages/zoosper-media');
    expect($source)->toContain('compatibility symlink');
    expect($source)->toContain('zoosper/media');
    expect($source)->toContain('composer update zoosper/media');
});

test('media path repository verifier checks package and root composer wiring', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/tools/verify-media-path-repository-pilot.php');

    expect($source)->toContain('packages/zoosper-media/module.php');
    expect($source)->toContain('packages/zoosper-media/composer.json');
    expect($source)->toContain("composer['require']['zoosper/media']");
    expect($source)->toContain('hasMediaRepository');
});
