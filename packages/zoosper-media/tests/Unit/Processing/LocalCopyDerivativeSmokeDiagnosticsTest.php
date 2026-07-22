<?php

declare(strict_types=1);

namespace Packages\zoospermedia\tests\Unit\Processing;

test('local copy derivative smoke prints processing errors and derivative entries', function () {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/tools/smoke-local-copy-derivative-generation.php');

    expect($source)->toContain('processing errors');
    expect($source)->toContain('derivative result entries');
    expect($source)->toContain('function resultErrors');
    expect($source)->toContain('function resultDerivatives');
    expect($source)->toContain('return false;');
});
