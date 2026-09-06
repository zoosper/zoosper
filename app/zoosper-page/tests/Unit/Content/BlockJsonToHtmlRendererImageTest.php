<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Content;

use Zoosper\Core\Editor\EditorImageBlockSanitizerInterface;
use Zoosper\Page\Content\BlockJsonToHtmlRenderer;

function phase13B6ImageSanitizer(): EditorImageBlockSanitizerInterface
{
    return new class implements EditorImageBlockSanitizerInterface {
        public function sanitise(array $data): ?array
        {
            $file = $data['file'] ?? null;
            $url = is_array($file) ? trim((string) ($file['url'] ?? '')) : '';
            if ($url === '' || !str_starts_with($url, '/media/')) {
                return null;
            }

            return [
                'url' => $url,
                'caption' => trim((string) ($data['caption'] ?? '')),
                'withBorder' => (bool) ($data['withBorder'] ?? false),
                'withBackground' => (bool) ($data['withBackground'] ?? false),
                'stretched' => (bool) ($data['stretched'] ?? false),
            ];
        }
    };
}

test('BlockJsonToHtmlRenderer renders managed Editor.js image blocks', function () {
    $html = (new BlockJsonToHtmlRenderer(phase13B6ImageSanitizer()))->render([
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
    $html = (new BlockJsonToHtmlRenderer(phase13B6ImageSanitizer()))->render([
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

test('page renderer service resolves the optional image sanitizer contract when available', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/app/zoosper-page/config/services.php');

    expect($source)->toContain(EditorImageBlockSanitizerInterface::class);
    expect($source)->toContain('new BlockJsonToHtmlRenderer(');
    expect($source)->toContain('$services->has(EditorImageBlockSanitizerInterface::class)');
});










