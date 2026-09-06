<?php

declare(strict_types=1);

namespace Packages\zoospermedia\tests\Unit\EditorJs;

use Zoosper\Core\Editor\EditorImageBlockSanitizerInterface;
use Zoosper\Core\Editor\EditorImageBlockValidatorInterface;
use Zoosper\Media\EditorJs\EditorJsImageBlockSanitizer;

test('sanitises allowed media image block data', function () {
    $sanitizer = new EditorJsImageBlockSanitizer();
    expect($sanitizer)->toBeInstanceOf(EditorImageBlockSanitizerInterface::class)
        ->and($sanitizer)->toBeInstanceOf(EditorImageBlockValidatorInterface::class);
    $data = $sanitizer->sanitise([
        'file' => ['url' => '/media/asset.png'],
        'caption' => '  Caption  ',
        'withBorder' => true,
        'withBackground' => false,
        'stretched' => true,
    ]);

    expect($data)->not->toBeNull();
    expect($data['url'])->toBe('/media/asset.png');
    expect($data['caption'])->toBe('Caption');
    expect($data['withBorder'])->toBeTrue();
    expect($data['stretched'])->toBeTrue();
});

test('rejects non media urls from image block data', function () {
    $data = (new EditorJsImageBlockSanitizer())->sanitise([
        'file' => ['url' => 'https://example.test/image.png'],
    ]);

    expect($data)->toBeNull();
});











