<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Processing;

test('local media derivative foundation audit locks processor-readiness signals', function () {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/tools/audit-local-media-derivative-foundation.php');

    expect($source)->toContain('local media derivative foundation audit');
    expect($source)->toContain('local derivative path resolver exists');
    expect($source)->toContain('local derivative writer exists');
    expect($source)->toContain('media processor interface exists');
    expect($source)->toContain('storage/media/derivatives/');
});
