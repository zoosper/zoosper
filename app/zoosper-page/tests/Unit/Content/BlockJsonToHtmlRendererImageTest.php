<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Content;

use Zoosper\Media\EditorJs\EditorJsImageBlockSanitizer;
use Zoosper\Page\Content\BlockJsonToHtmlRenderer;

test('BlockJsonToHtmlRenderer renders managed Editor.js image blocks', function () {
    $html = (new BlockJsonToHtmlRenderer(new EditorJsImageBlockSanitizer()))->render([
        'blocks' => [[
            'type' => 'image',
            'data' => [
                'file' => ['url' => '/media/2026/07/example.png'],
                'caption' => 'Example image',
                'withBorder' => true,
                'withBackground' => false,
                'stretched' => true,
            ],
        ]],
    ]);

    expect($html)->toContain('<figure class="cms-image cms-image--bordered cms-image--stretched">');
    expect($html)->toContain('<img src="/media/2026/07/example.png" alt="Example image" loading="lazy">');
    expect($html)->toContain('<figcaption>Example image</figcaption>');
});

test('BlockJsonToHtmlRenderer ignores remote image block URLs', function () {
    $html = (new BlockJsonToHtmlRenderer(new EditorJsImageBlockSanitizer()))->render([
        'blocks' => [[
            'type' => 'image',
            'data' => [
                'file' => ['url' => 'https://example.test/image.png'],
                'caption' => 'Remote image',
            ],
        ]],
    ]);

    expect($html)->toBe('');
});

test('page renderer service injects media image sanitizer when available', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/app/zoosper-page/config/services.php');

    expect($source)->toContain(EditorJsImageBlockSanitizer::class);
    expect($source)->toContain('new BlockJsonToHtmlRenderer(');
    expect($source)->toContain('$services->has(EditorJsImageBlockSanitizer::class)');
});
