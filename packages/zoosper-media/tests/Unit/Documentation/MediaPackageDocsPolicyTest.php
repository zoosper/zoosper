<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Documentation;

test('media package owns its package documentation index', function () {
    $root = dirname(__DIR__, 3);
    $readme = (string) file_get_contents($root . '/docs/README.md');

    expect($readme)->toContain('Zoosper Media documentation');
    expect($readme)->toContain('Root project docs should link here');
});

test('media package documentation policy lists media-owned documentation types', function () {
    $root = dirname(__DIR__, 3);
    $doc = (string) file_get_contents($root . '/docs/architecture/media-package-documentation-policy.md');

    expect($doc)->toContain('media upload validation');
    expect($doc)->toContain('Editor.js media upload contract');
    expect($doc)->toContain('future media-gd and media-imagick');
});
