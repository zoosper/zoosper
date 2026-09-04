<?php

declare(strict_types=1);

use Zoosper\Core\Editor\EditorImageToolConfigInterface;
use Zoosper\Media\EditorJs\EditorJsImageToolConfig;

it('keeps optional editor image configuration behind a Core-owned contract', function (): void {
    $config = new EditorJsImageToolConfig('/admin/media/editorjs/upload');

    expect($config)->toBeInstanceOf(EditorImageToolConfigInterface::class)
        ->and($config->toArray('token')['endpoints']['byFile'])
        ->toBe('/admin/media/editorjs/upload');
});

it('keeps the Editor package free of concrete Media references', function (): void {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/app/zoosper-editor/src/EditorJsContentEditor.php');
    $services = (string) file_get_contents($root . '/app/zoosper-editor/config/services.php');
    $composer = (string) file_get_contents($root . '/app/zoosper-editor/composer.json');

    expect($source)->toContain(EditorImageToolConfigInterface::class)
        ->not->toContain('Zoosper\\Media\\')
        ->and($services)->toContain(EditorImageToolConfigInterface::class)
        ->not->toContain('Zoosper\\Media\\')
        ->and($composer)->not->toContain('zoosper/media');
});
