<?php
declare(strict_types=1);
it('declares Marko Core directly and prevents a second new general interceptor framework', function (): void {
    $root=dirname(__DIR__,5);
    $composer=json_decode((string)file_get_contents($root.'/app/zoosper-core/composer.json'),true,512,JSON_THROW_ON_ERROR);
    expect($composer['require']['marko/core']??null)->toBe('^0.8');
    $decision=(string)file_get_contents($root.'/docs/architecture-decisions/marko-extensibility-ownership.md');
    expect($decision)->toContain('Marko Plugins are the canonical future general method-interception runtime')
        ->toContain('Zoosper entity-save lifecycle remains separate')
        ->toContain('Zoosper general events remain authoritative');
});
