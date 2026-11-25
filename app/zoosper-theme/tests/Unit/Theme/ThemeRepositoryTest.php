<?php

declare(strict_types=1);

use Zoosper\Theme\Theme\ThemeRepository;

it('discovers and sorts themes and handles paths without matches', function (): void {
    $themes = new ThemeRepository(dirname(__DIR__, 5) . '/themes');
    $all = $themes->all();
    $names = array_column($all, 'name');
    $sorted = $names;
    sort($sorted);

    expect($all)->not->toBeEmpty()
        ->and($names)->toBe($sorted)
        ->and($themes->exists('default'))->toBeTrue()
        ->and($themes->exists('missing-theme'))->toBeFalse()
        ->and((new ThemeRepository(dirname(__DIR__, 5) . '/nonexistent-theme-directory'))->all())->toBe([]);
});