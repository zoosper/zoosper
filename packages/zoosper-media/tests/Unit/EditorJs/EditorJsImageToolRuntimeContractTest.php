<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\EditorJs;

use Zoosper\Media\EditorJs\EditorJsImageToolConfig;

test('image tool runtime config provides endpoint field and csrf header', function () {
    $config = (new EditorJsImageToolConfig())->toArray('token-123');

    expect($config['endpoints']['byFile'])->toBe('/admin/media/editorjs/upload');
    expect($config['field'])->toBe('image');
    expect($config['additionalRequestHeaders']['X-CSRF-Token'])->toBe('token-123');
});
