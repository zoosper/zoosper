<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Content;

use Zoosper\Page\Content\BlockJsonToHtmlRenderer;

test('renders supported Editor.js blocks as conservative HTML', function () {
    $html = (new BlockJsonToHtmlRenderer())->render([
        'blocks' => [
            ['type' => 'header', 'data' => ['text' => 'Heading', 'level' => 2]],
            ['type' => 'paragraph', 'data' => ['text' => 'Paragraph text']],
            ['type' => 'list', 'data' => ['style' => 'ordered', 'items' => [
                ['content' => 'First'],
                ['content' => 'Second'],
            ]]],
        ],
    ]);

    expect($html)->toContain('<h2>Heading</h2>');
    expect($html)->toContain('<p>Paragraph text</p>');
    expect($html)->toContain('<ol><li>First</li><li>Second</li></ol>');
});

test('escapes block text before generating frontend HTML', function () {
    $html = (new BlockJsonToHtmlRenderer())->render([
        'blocks' => [
            ['type' => 'paragraph', 'data' => ['text' => '<script>alert(1)</script>']],
            ['type' => 'header', 'data' => ['text' => '<em>Title</em>', 'level' => 3]],
            ['type' => 'list', 'data' => ['style' => 'unordered', 'items' => [
                ['content' => '<img src=x onerror=alert(1)>'],
            ]]],
        ],
    ]);

    expect($html)->not->toContain('<script>');
    expect($html)->not->toContain('<em>Title</em>');
    expect($html)->not->toContain('<img');
    expect($html)->toContain('&lt;script&gt;alert(1)&lt;/script&gt;');
    expect($html)->toContain('&lt;em&gt;Title&lt;/em&gt;');
    expect($html)->toContain('&lt;img src=x onerror=alert(1)&gt;');
});

test('ignores unsupported blocks and empty fragments', function () {
    $html = (new BlockJsonToHtmlRenderer())->render([
        'blocks' => [
            ['type' => 'video', 'data' => ['url' => 'https://example.test/video.mp4']],
            ['type' => 'paragraph', 'data' => ['text' => '']],
        ],
    ]);

    expect($html)->toBe('');
});
