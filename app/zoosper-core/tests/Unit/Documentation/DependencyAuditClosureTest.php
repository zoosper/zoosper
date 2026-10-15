<?php

declare(strict_types=1);

it('blocks CI on locked PHP and Node dependency audits', function (): void {
    $root = dirname(__DIR__, 5);
    $workflow = (string) file_get_contents($root . '/.github/workflows/quality-gate.yml');

    expect($workflow)
        ->toContain('composer audit --locked --no-interaction')
        ->toContain('npm audit --package-lock-only --include=dev --audit-level=high');
});

it('keeps C4.3 partial until the private reporting path is owner verified', function (): void {
    $root = dirname(__DIR__, 5);
    $roadmap = (string) file_get_contents($root . '/ROADMAP.md');
    $checklist = (string) file_get_contents($root . '/docs/release-checklist.md');

    expect($roadmap)
        ->toContain('- [~] **C4.3 External dependency and disclosure review.**')
        ->toContain('owner-verified exercise of `security@zoosper.com`')
        ->and($checklist)
        ->toContain('Exercise the private `security@zoosper.com` reporting path')
        ->toContain('Keep C4.2 target-environment production acceptance open');
});

it('retains the reviewed source-map-js lock version', function (): void {
    $root = dirname(__DIR__, 5);
    $lock = json_decode(
        (string) file_get_contents($root . '/package-lock.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );

    expect($lock['packages']['node_modules/source-map-js']['version'] ?? null)->toBe('1.2.2');
});
