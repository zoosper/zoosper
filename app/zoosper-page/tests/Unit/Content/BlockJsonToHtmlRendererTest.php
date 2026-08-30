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
            ['type' => 'quote', 'data' => ['text' => 'Inspiring quote', 'caption' => 'Author Name', 'alignment' => 'center']],
            ['type' => 'delimiter', 'data' => []],
            ['type' => 'code', 'data' => ['code' => 'const x = 10;']],
            ['type' => 'table', 'data' => [
                'withHeadings' => true,
                'content' => [
                    ['Header 1', 'Header 2'],
                    ['Cell 1', 'Cell 2'],
                ],
            ]],
            ['type' => 'raw', 'data' => ['html' => '<div>safe snippet</div>']],
        ],
    ]);

    expect($html)->toContain('<h2>Heading</h2>');
    expect($html)->toContain('<p>Paragraph text</p>');
    expect($html)->toContain('<ol><li>First</li><li>Second</li></ol>');
    expect($html)->toContain('<blockquote class="cms-quote--center"><p>Inspiring quote</p><cite>Author Name</cite></blockquote>');
    expect($html)->toContain('<hr class="cms-delimiter">');
    expect($html)->toContain('<pre class="cms-code"><code>const x = 10;</code></pre>');
    expect($html)->toContain('<table class="cms-table"><tr><th>Header 1</th><th>Header 2</th></tr><tr><td>Cell 1</td><td>Cell 2</td></tr></table>');
    expect($html)->toContain('&lt;div&gt;safe snippet&lt;/div&gt;');
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
