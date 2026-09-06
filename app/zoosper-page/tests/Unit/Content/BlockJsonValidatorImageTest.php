<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Content;

use Zoosper\Core\Editor\EditorImageBlockValidatorInterface;
use Zoosper\Page\Content\BlockJsonValidator;

function phase13B7ImageValidator(): EditorImageBlockValidatorInterface
{
    return new class implements EditorImageBlockValidatorInterface {
        public function validate(array $data): array
        {
            $file = $data['file'] ?? null;
            if (!is_array($file)) {
                return ['image file must be an object.'];
            }
            $url = trim((string) ($file['url'] ?? ''));
            return $url !== '' && str_starts_with($url, '/media/')
                ? []
                : ['image URL must use managed /media/ storage.'];
        }
    };
}

test('BlockJsonValidator accepts image blocks through an optional contributor', function (): void {
    $result = (new BlockJsonValidator([], phase13B7ImageValidator()))->validate([
        'blocks' => [['type' => 'image', 'data' => ['file' => ['url' => '/media/2026/07/example.png']]]],
    ]);
    expect($result->valid)->toBeTrue()->and($result->errors)->toBe([]);
});

test('BlockJsonValidator rejects image blocks without a contributor', function (): void {
    $result = (new BlockJsonValidator())->validate([
        'blocks' => [['type' => 'image', 'data' => ['file' => ['url' => '/media/2026/07/example.png']]]],
    ]);
    expect($result->valid)->toBeFalse()->and(implode(' ', $result->errors))->toContain('unsupported type: image');
});

test('BlockJsonValidator reports contributor validation errors', function (): void {
    $result = (new BlockJsonValidator([], phase13B7ImageValidator()))->validate([
        'blocks' => [['type' => 'image', 'data' => ['file' => ['url' => 'https://example.test/image.png']]]],
    ]);
    expect($result->valid)->toBeFalse()->and(implode(' ', $result->errors))->toContain('/media/');
});
