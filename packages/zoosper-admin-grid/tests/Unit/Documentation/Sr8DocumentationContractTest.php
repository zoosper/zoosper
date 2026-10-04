<?php

declare(strict_types=1);

it('documents the executable Admin Grid DOM and clean audit boundary', function (): void {
    $root = dirname(__DIR__, 5);
    $roadmap = (string) file_get_contents($root . '/ROADMAP.md');
    $changelog = (string) file_get_contents($root . '/CHANGELOG.md');
    $package = json_decode((string) file_get_contents($root . '/package.json'), true, 512, JSON_THROW_ON_ERROR);

    expect($roadmap)
        ->toContain('JavaScript behaviour/syntax gates')
        ->not->toContain('Remaining: add DOM coverage')
        ->and($changelog)
        ->toContain('Closed SR-8 Admin Grid DOM behaviour coverage')
        ->toContain("Node's built-in test runner")
        ->toContain('locked jsdom suite')
        ->toContain('live table reflection')
        ->and($package['overrides']['nanoid'] ?? null)->toBe('3.3.18')
        ->and($package['overrides']['postcss'] ?? null)->toBe('8.5.23');
});
