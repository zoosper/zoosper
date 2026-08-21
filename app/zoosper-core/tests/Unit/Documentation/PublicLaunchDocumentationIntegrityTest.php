<?php

declare(strict_types=1);

it('publishes the current alpha release and delivered product surface at the repository front door', function (): void {
    $root = dirname(__DIR__, 5);
    $readme = (string) file_get_contents($root . '/README.md');

    expect($readme)
        ->toContain('v0.3.0-alpha.2')
        ->toContain('v0.3.0-alpha.3-dev')
        ->toContain('zoosper-menu')
        ->toContain('revision listing and revision restoration')
        ->toContain('CI and the tracked pre-push hook')
        ->toContain('[feature guides](docs/guide/index.md)')
        ->toContain('Explicitly not complete')
        ->not->toContain('Post-Phase 1.41 hardening and Marko adoption (2026-07-30/31)');
});

it('states the tagged pre-release and stable-release status precisely', function (): void {
    $root = dirname(__DIR__, 5);
    $security = (string) file_get_contents($root . '/SECURITY.md');

    expect($security)
        ->toContain('latest tagged pre-release is `v0.3.0-alpha.2`')
        ->toContain('No stable release has shipped')
        ->toContain('`composer.json` and `composer.lock` are the source of truth')
        ->not->toContain('no tagged stable releases have shipped yet');
});

it('records the current review priorities and does not overclaim media derivatives', function (): void {
    $root = dirname(__DIR__, 5);
    $roadmap = (string) file_get_contents($root . '/ROADMAP.md');

    expect($roadmap)
        ->toContain('Last updated:** 2026-08-21 (Sydney)')
        ->toContain('External review response and public-launch priorities (2026-08-11)')
        ->toContain('Duplicate MediaUploadService construction is resolved')
        ->toContain('Derivative database persistence remains a separate follow-up')
        ->toContain('Resume Phase 9HF Marko dashboard widget adoption')
        ->not->toContain('Page admin decoupling is partial')
        ->not->toContain('Page admin-decoupling is still partial');
});
