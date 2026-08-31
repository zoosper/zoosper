<?php

declare(strict_types=1);

it('gates dev changes through the enforceable quality contract', function (): void {
    $root = dirname(__DIR__, 5);
    $workflow = (string) file_get_contents($root . '/.github/workflows/quality-gate.yml');
    expect($workflow)->toContain('branches: [dev]')
        ->toContain('contents: read')
        ->toContain('cancel-in-progress: true')
        ->toContain('php-version: "8.5"')
        ->toContain('composer validate --no-check-publish --strict')
        ->toContain('composer audit --locked')
        ->toContain('composer ci:js')
        ->toContain('composer gate:strict')
        ->toContain('continue-on-error: true')
        ->toContain('composer analyse')
        ->toContain('Psalm advisory outcome')
        ->toContain('composer test')
        ->toContain('composer compile');
});

it('keeps CI commands available locally through Composer', function (): void {
    $root = dirname(__DIR__, 5);
    $composer = json_decode((string) file_get_contents($root . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    expect($composer['scripts'])->toHaveKeys(['test', 'compile', 'gate:strict', 'analyse', 'ci:js']);
});










