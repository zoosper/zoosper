<?php

declare(strict_types=1);

it('publishes the current alpha release and delivered product surface at the repository front door', function (): void {
    $root = dirname(__DIR__, 5);
    $readme = (string) file_get_contents($root . '/README.md');

    expect($readme)
        ->toContain('v0.3.2-alpha.3')
        ->toContain('Latest immutable pre-release: `v0.3.2-alpha.4`')
        ->not->toContain('Release candidate under verification')
        ->toContain('v0.3.2-alpha.4')
        ->toContain('zoosper-menu')
        ->toContain('revision listing and revision restoration')
        ->toContain('CI runs Psalm as a blocking full-scope gate')
        ->toContain('[documentation index](docs/README.md)')
        ->toContain('Current development work')
        ->not->toContain('CI test suite execution against an active MySQL service container is being finalized')
        ->not->toContain('Automated secret generation and comprehensive boot-time production validation are being finalized')
        ->not->toContain('Absolute session lifetime controls and concurrent session limits are in progress')
        ->not->toContain('docs/guide/')
        ->not->toContain('Post-Phase 1.41 hardening and Marko adoption (2026-07-30/31)');
});

it('states the tagged pre-release and stable-release status precisely', function (): void {
    $root = dirname(__DIR__, 5);
    $security = (string) file_get_contents($root . '/SECURITY.md');

    expect($security)
        ->toContain('latest immutable pre-release is `v0.3.2-alpha.4`')
        ->toContain('supported `dev` branch continues closure work from the published alpha.4 baseline')
        ->not->toContain('release candidate while its release gates are completed')
        ->toContain('No stable release has shipped')
        ->toContain('`composer.json` and `composer.lock` are the source of truth')
        ->not->toContain('no tagged stable releases have shipped yet');
});

it('records the current review priorities and does not overclaim media derivatives', function (): void {
    $root = dirname(__DIR__, 5);
    $roadmap = (string) file_get_contents($root . '/ROADMAP.md');

    expect($roadmap)
        ->toContain('**Last updated:** 2026-10-09 (Sydney)')
        ->toContain('## Required remaining work')
        ->toContain('## Explicitly not required for closure')
        ->toContain('Current Psalm baseline: `1,039` entries')
        ->toContain('Media queue observability')
        ->not->toContain('External review response and public-launch priorities (2026-08-11)')
        ->not->toContain('Phase 10AR')
        ->not->toContain('This phase was not deployed.')
        ->not->toContain('Page admin decoupling is partial')
        ->not->toContain('Page admin-decoupling is still partial');
});










