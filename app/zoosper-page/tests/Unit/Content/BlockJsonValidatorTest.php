<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Content;

/**
 * Regression tests for the restricted Editor.js block JSON validator.
 *
 * Phase 1.21 - First regression suite (co-located in zoosper-page).
 *
 * Guards the currently-supported block shape (paragraph, header, list),
 * including the heading-level allow-list and the maximum list nesting depth.
 * Built against the real BlockJsonValidator / BlockJsonValidationResult API.
 */

use Zoosper\Page\Content\BlockJsonValidationResult;
use Zoosper\Page\Content\BlockJsonValidator;

test('valid paragraph, header and list blocks pass', function () {
    // Arrange
    $validator = new BlockJsonValidator();
    $document  = [
        'blocks' => [
            ['type' => 'paragraph', 'data' => ['text' => 'Hello world']],
            ['type' => 'header',    'data' => ['text' => 'Title', 'level' => 2]],
            ['type' => 'list',      'data' => [
                'style' => 'unordered',
                'items' => [
                    ['content' => 'a'],
                    ['content' => 'b'],
                ],
            ]],
        ],
    ];

    // Act
    $result = $validator->validate($document);

    // Assert
    expect($result)->toBeInstanceOf(BlockJsonValidationResult::class);
    expect($result->valid)->toBeTrue();
    expect($result->errors)->toBe([]);
});

test('a document missing the blocks array fails', function () {
    // Arrange
    $validator = new BlockJsonValidator();

    // Act
    $result = $validator->validate(['foo' => 'bar']);

    // Assert
    expect($result->valid)->toBeFalse();
    expect($result->errors)->not->toBe([]);
});

test('a header with a disallowed level fails', function () {
    // Arrange - level 1 is outside the default allow-list [2, 3, 4].
    $validator = new BlockJsonValidator();
    $document  = [
        'blocks' => [
            ['type' => 'header', 'data' => ['text' => 'Nope', 'level' => 1]],
        ],
    ];

    // Act
    $result = $validator->validate($document);

    // Assert
    expect($result->valid)->toBeFalse();
});

test('list nesting beyond the max depth fails', function () {
    // Arrange - top-level items are depth 1, so nest to depth 4 (> default 3).
    $validator = new BlockJsonValidator();

    $deepItem = ['content' => 'l4'];                          // depth 4
    $level3   = ['content' => 'l3', 'items' => [$deepItem]];  // depth 3
    $level2   = ['content' => 'l2', 'items' => [$level3]];    // depth 2
    $level1   = ['content' => 'l1', 'items' => [$level2]];    // depth 1

    $document = [
        'blocks' => [
            ['type' => 'list', 'data' => ['style' => 'unordered', 'items' => [$level1]]],
        ],
    ];

    // Act
    $result = $validator->validate($document);

    // Assert
    expect($result->valid)->toBeFalse();
});

test('an unsupported block type fails', function () {
    // Arrange
    $validator = new BlockJsonValidator();
    $document  = [
        'blocks' => [
            ['type' => 'video', 'data' => ['url' => 'https://example.com/v.mp4']],
        ],
    ];

    // Act
    $result = $validator->validate($document);

    // Assert
    expect($result->valid)->toBeFalse();
});











it('rejects associative block collections instead of treating their keys as positions', function (): void {
    $result = (new BlockJsonValidator())->validate([
        'blocks' => [
            'first' => ['type' => 'paragraph', 'data' => ['text' => 'Hello']],
        ],
    ]);

    expect($result->valid)->toBeFalse()
        ->and(implode(' ', $result->errors))->toContain('blocks list');
});

it('rejects associative top-level and nested list item collections', function (): void {
    $validator = new BlockJsonValidator();
    $topLevel = $validator->validate([
        'blocks' => [[
            'type' => 'list',
            'data' => ['items' => ['first' => 'Item']],
        ]],
    ]);
    $nested = $validator->validate([
        'blocks' => [[
            'type' => 'list',
            'data' => ['items' => [['content' => 'Parent', 'items' => ['child' => 'Item']]]],
        ]],
    ]);

    expect($topLevel->valid)->toBeFalse()
        ->and(implode(' ', $topLevel->errors))->toContain('list items must be a list')
        ->and($nested->valid)->toBeFalse()
        ->and(implode(' ', $nested->errors))->toContain('nested list items must be a list');
});

it('falls back to the supported block types for malformed configured type lists', function (): void {
    $result = (new BlockJsonValidator(['allowed_types' => ['paragraph', 42]]))->validate([
        'blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Hello']]],
    ]);

    expect($result->valid)->toBeTrue();
});
