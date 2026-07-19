<?php

declare(strict_types=1);

namespace Zoosper\Admin\Tests\Unit\Editor;

test('media schema runtime diagnostic explains missing media assets table fix', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/tools/diagnose-media-schema-runtime.php');

    expect($source)->toContain('media_assets');
    expect($source)->toContain('PHP=php8.5 bin/zoosper migrate');
    expect($source)->toContain('diagnose-media-schema-runtime.php');
});
