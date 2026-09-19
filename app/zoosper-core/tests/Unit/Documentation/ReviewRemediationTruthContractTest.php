<?php

declare(strict_types=1);

it('keeps completed review remediation aligned with deployed source history', function (): void {
    $root = dirname(__DIR__, 5);
    $roadmap = (string) file_get_contents($root . '/ROADMAP.md');
    $changelog = (string) file_get_contents($root . '/CHANGELOG.md');

    expect($roadmap)
        ->toContain('**Last updated:** 2026-09-19 (Sydney)')
        ->toContain('SR-6 GenerateSecrets environment-file hardening')
        ->toContain('account lockout ✅')
        ->toContain('password reset ✅')
        ->toContain('bounded prerelease constraints')
        ->toContain('Durable-tool ownership is explicit and enforced')
        ->toContain('Root tooling is reduced to the bounded operational set')
        ->toContain('Page Momentum production surface retired')
        ->not->toContain('This phase was not deployed.')
        ->not->toContain('Still open:' . PHP_EOL . '   account lockout and password reset.')
        ->not->toContain('~150+ single-purpose tooling scripts still in `tools/`')
        ->and($changelog)
        ->toContain('Reconciled reviewer and release truth after SR-6')
        ->not->toContain('redact secret values.\\n- Closed MED-02');
});
