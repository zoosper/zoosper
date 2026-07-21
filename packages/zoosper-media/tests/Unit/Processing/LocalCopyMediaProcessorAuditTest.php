<?php

declare(strict_types=1);

namespace Packages\zoospermedia\tests\Unit\Processing;

test('local copy media processor audit documents engine-free derivative semantics', function () {
    $root = dirname(__DIR__, 3);
    $tool = (string) file_get_contents($root . '/tools/audit-local-copy-media-processor.php');
    $source = (string) file_get_contents($root . '/src/Processing/LocalCopyMediaProcessor.php');

    expect($tool)->toContain('LocalCopyMediaProcessor exists');
    expect($tool)->toContain('MediaProcessorInterface is implemented');
    expect($tool)->toContain('processor keeps originals immutable');
    expect($source)->toContain('implements MediaProcessorInterface');
    expect($source)->toContain('performs no resize, crop, re-encode or metadata');
    expect($source)->toContain('bytes are deliberately unchanged');
});

test('local copy media processor keeps unsafe input checks source-owned', function () {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Processing/LocalCopyMediaProcessor.php');

    expect($source)->toContain('Unsafe source media storage path');
    expect($source)->toContain('Source media path is not under the configured original storage root');
    expect($source)->not->toContain('file_put_contents($source');
});
