<?php

declare(strict_types=1);

it('bundles live reflection into the rendered column drag bridge', function (): void {
    $path = dirname(__DIR__, 3) . '/resources/assets/js/zoosper-grid-column-drag.js';
    $source = file_get_contents($path);

    expect($source)->not->toBeFalse();
    expect($source)
        ->toContain('Zoosper Phase 4ZE: live table reflection')
        ->toContain('[data-grid-column-list]')
        ->toContain('data-grid-column')
        ->toContain('MutationObserver')
        ->toContain('requestAnimationFrame')
        ->toContain('Unsaved changes')
        ->not->toContain('cellIndex');
});
