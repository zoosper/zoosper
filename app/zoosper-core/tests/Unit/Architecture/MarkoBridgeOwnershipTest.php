<?php
declare(strict_types=1);
it('keeps Marko extensibility behind a future focused Zoosper bridge', function (): void {
    $root=dirname(__DIR__,5);
    $composer=json_decode((string)file_get_contents($root.'/app/zoosper-core/composer.json'),true,512,JSON_THROW_ON_ERROR);
    expect($composer['require'])->not->toHaveKey('marko/core');
    $decision=(string)file_get_contents($root.'/docs/architecture-decisions/marko-extensibility-ownership.md');
    expect($decision)->toContain('Zoosper retains bridge-first package ownership')
        ->toContain('preferred initial boundary is `zoosper/extensibility`')
        ->toContain('Do not add direct `marko/core` ownership to `zoosper/core`');
    expect(is_dir($root.'/app/zoosper-core/tests/Unit/Marko'))->toBeFalse();
});
