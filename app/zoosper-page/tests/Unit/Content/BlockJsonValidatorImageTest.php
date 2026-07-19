<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Content;

use Zoosper\Page\Content\BlockJsonValidator;

test('BlockJsonValidator accepts managed Editor.js image blocks', function () {
    $result = (new BlockJsonValidator())->validate([
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

    expect($result->valid)->toBeTrue();
    expect($result->errors)->toBe([]);
});

test('BlockJsonValidator rejects remote image URLs', function () {
    $result = (new BlockJsonValidator())->validate([
        'blocks' => [[
            'type' => 'image',
            'data' => [
                'file' => ['url' => 'https://example.test/image.png'],
            ],
        ]],
    ]);

    expect($result->valid)->toBeFalse();
    expect(implode(' ', $result->errors))->toContain('/media/');
});

test('BlockJsonValidator keeps image support when config allowed types are older', function () {
    $result = (new BlockJsonValidator(['allowed_types' => ['paragraph', 'header', 'list']]))->validate([
        'blocks' => [[
            'type' => 'image',
            'data' => [
                'file' => ['url' => '/media/2026/07/example.png'],
            ],
        ]],
    ]);

    expect($result->valid)->toBeTrue();
});
