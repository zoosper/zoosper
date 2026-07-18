<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\EditorJs;

use Zoosper\Media\EditorJs\EditorJsImageBlockSanitizer;

test('media package registers image block sanitizer service', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/packages/zoosper-media/config/services.php');

    expect($source)->toContain(EditorJsImageBlockSanitizer::class);
    expect($source)->toContain('new EditorJsImageBlockSanitizer()');
});
