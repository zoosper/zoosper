<?php

declare(strict_types=1);

namespace Zoosper\Admin\Tests\Unit\Editor;

test('browser smoke diagnostics cover editorjs media runtime assumptions', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/tools/diagnose-editorjs-media-browser-smoke.php');

    expect($source)->toContain('/admin/media/editorjs/upload');
    expect($source)->toContain('field=image');
    expect($source)->toContain('X-CSRF-Token');
    expect($source)->toContain('success=1');
    expect($source)->toContain('/media/...');
});

test('editorjs admin css includes image tool containment polish', function () {
    $root = dirname(__DIR__, 5);
    $css = (string) file_get_contents($root . '/public/assets/admin/css/zoosper-content-editor.css');

    expect($css)->toContain('.zoosper-content-editor .image-tool');
    expect($css)->toContain('max-width: 100%');
    expect($css)->toContain('image-tool--withBorder');
    expect($css)->toContain('image-tool--withBackground');
    expect($css)->toContain('image-tool--stretched');
});

test('phase documentation records the browser validation checklist', function () {
    $root = dirname(__DIR__, 5);
    $doc = (string) file_get_contents($root . '/docs/operations/editorjs-media-browser-smoke.md');

    expect($doc)->toContain('/admin/pages/create');
    expect($doc)->toContain('Network');
    expect($doc)->toContain('POST /admin/media/editorjs/upload');
    expect($doc)->toContain('X-CSRF-Token');
    expect($doc)->toContain('invalid file type');
});
