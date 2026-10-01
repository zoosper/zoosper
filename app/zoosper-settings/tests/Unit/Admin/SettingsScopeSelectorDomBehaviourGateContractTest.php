<?php

declare(strict_types=1);

it('gates canonical Settings scope-selector DOM behaviour in npm Composer and CI', function (): void {
    $root = dirname(__DIR__, 5);
    $package = json_decode((string) file_get_contents($root . '/package.json'), true, 512, JSON_THROW_ON_ERROR);
    $composer = json_decode((string) file_get_contents($root . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $workflow = (string) file_get_contents($root . '/.github/workflows/quality-gate.yml');
    $suite = $root . '/app/zoosper-settings/tests/Browser/settings-scope-selector-dom.test.js';

    expect($package['devDependencies']['jsdom'] ?? null)->toBeString()->not->toBe('')
        ->and($package['scripts']['test:settings-scope-dom'] ?? null)
        ->toBe('node --test app/zoosper-settings/tests/Browser/settings-scope-selector-dom.test.js')
        ->and($composer['scripts']['test:settings-scope-dom'] ?? null)->toBe('npm run test:settings-scope-dom')
        ->and($workflow)->toContain('Run Settings scope selector DOM behaviour suite')
        ->toContain('run: composer test:settings-scope-dom')
        ->and($suite)->toBeFile();
});
