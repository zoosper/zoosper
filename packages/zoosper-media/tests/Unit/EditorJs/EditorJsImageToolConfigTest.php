<?php

declare(strict_types=1);

namespace Packages\zoospermedia\tests\Unit\EditorJs;

use Zoosper\Media\EditorJs\EditorJsImageToolConfig;

test('builds Editor.js image tool config with upload endpoint and csrf header', function () {
    $config = (new EditorJsImageToolConfig())->toArray('csrf-token');

    expect($config['endpoints']['byFile'])->toBe('/admin/media/editorjs/upload');
    expect($config['field'])->toBe('image');
    expect($config['types'])->toBe('image/*');
    expect($config['additionalRequestHeaders']['X-CSRF-Token'])->toBe('csrf-token');
    expect($config['features']['caption'])->toBeTrue();
});
