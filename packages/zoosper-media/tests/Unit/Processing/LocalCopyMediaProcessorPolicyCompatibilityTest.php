<?php

declare(strict_types=1);

namespace Packages\zoospermedia\tests\Unit\Processing;

use Zoosper\Media\Processing\LocalCopyMediaProcessor;

test('local copy processor tolerates media processing policy without original storage root method', function () {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Processing/LocalCopyMediaProcessor.php');

    expect(class_exists(LocalCopyMediaProcessor::class))->toBeTrue();
    expect($source)->toContain('method_exists($policy, \'originalStorageRoot\')');
    expect($source)->toContain("DEFAULT_ORIGINAL_STORAGE_ROOT = 'storage/media/original'");
    expect($source)->not->toContain('$policy->originalStorageRoot();\n        $originalRoot');
});
