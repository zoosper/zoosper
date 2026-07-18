<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Composer;

test('media compatibility removal tool refuses to delete a real app module directory', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/tools/remove-media-app-compatibility.php');

    expect($source)->toContain('Refusing to delete source');
    expect($source)->toContain('is_link($compatPath)');
    expect($source)->toContain('unlink($compatPath)');
});

test('media independent discovery verifier requires package or vendor source', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/tools/verify-media-package-independent-discovery.php');

    expect($source)->toContain("['packages', 'vendor']");
    expect($source)->toContain('app/zoosper-media compatibility symlink removed');
    expect($source)->toContain('media module discovered');
});
