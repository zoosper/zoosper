<?php

declare(strict_types=1);

it('locks the final browser suites and retained static-contract policy', function (): void {
    $root = dirname(__DIR__, 5);
    $package = json_decode((string) file_get_contents($root . '/package.json'), true, 512, JSON_THROW_ON_ERROR);
    $composer = json_decode((string) file_get_contents($root . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $workflow = (string) file_get_contents($root . '/.github/workflows/quality-gate.yml');
    $roadmap = (string) file_get_contents($root . '/ROADMAP.md');
    $changelog = (string) file_get_contents($root . '/CHANGELOG.md');

    expect($package['scripts']['test:test-signal-closure-dom'] ?? null)
        ->toBe('node --test app/zoosper-page/tests/Browser/page-grid-workspace-dom.test.js packages/zoosper-audit/tests/Browser/audit-workspaces-dom.test.js')
        ->and($composer['scripts']['test:test-signal-closure-dom'] ?? null)->toBe('npm run test:test-signal-closure-dom')
        ->and($workflow)->toContain('Run final test-signal closure DOM suite')
        ->toContain('run: composer test:test-signal-closure-dom')
        ->and($roadmap)->toContain('## Required remaining work')
        ->toContain('Completed implementation history remains in the changelog and tags, not here.')
        ->and($changelog)->toContain('Closed the TS-1 test-signal remediation after a repository-wide risk inventory')
        ->toContain('architecture, security, release, documentation, CSS, schema and wiring contracts');
});
