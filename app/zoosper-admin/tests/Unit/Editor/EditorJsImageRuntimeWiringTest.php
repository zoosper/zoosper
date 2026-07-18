<?php

declare(strict_types=1);

namespace Zoosper\Admin\Tests\Unit\Editor;

use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Media\EditorJs\EditorJsImageToolConfig;

test('EditorJsContentEditor renders image tool config data attribute', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/app/zoosper-admin/src/Editor/EditorJsContentEditor.php');

    expect($source)->toContain(EditorJsImageToolConfig::class);
    expect($source)->toContain(CsrfTokenManager::class);
    expect($source)->toContain('data-zoosper-image-tool');
    expect($source)->toContain('$this->imageToolConfig->toArray($this->csrf->token())');
});

test('admin service factory injects image tool config and csrf into EditorJsContentEditor', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/app/zoosper-admin/config/services.php');

    expect($source)->toContain(EditorJsImageToolConfig::class);
    expect($source)->toContain('$services->has(EditorJsImageToolConfig::class)');
    expect($source)->toContain('$services->get(CsrfTokenManager::class)');
});

test('admin runtime registers Image Tool when bundle exposes it', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/public/assets/admin/js/zoosper-content-editor.js');

    expect($source)->toContain("parseJsonAttribute(wrapper, 'data-zoosper-image-tool')");
    expect($source)->toContain('bundle.ImageTool');
    expect($source)->toContain('tools.image');
    expect($source)->toContain('renderImageBlock');
});

test('admin editor bundle source exposes editorjs image tool', function () {
    $root = dirname(__DIR__, 5);
    $entry = (string) file_get_contents($root . '/assets/admin/editor/zoosper-editorjs-entry.js');
    $package = (string) file_get_contents($root . '/package.json');

    expect($entry)->toContain("import ImageTool from '@editorjs/image';");
    expect($entry)->toContain('ImageTool,');
    expect($package)->toContain('"@editorjs/image"');
});
