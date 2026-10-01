<?php

declare(strict_types=1);

it('gates canonical Permission Explorer DOM behaviour in npm Composer and CI', function (): void {
    $root = dirname(__DIR__, 4);
    $package = json_decode((string) file_get_contents($root . '/package.json'), true, 512, JSON_THROW_ON_ERROR);
    $composer = json_decode((string) file_get_contents($root . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $workflow = (string) file_get_contents($root . '/.github/workflows/quality-gate.yml');
    $suite = $root . '/app/zoosper-auth/tests/Browser/permission-explorer-dom.test.js';

    expect($package['devDependencies']['jsdom'] ?? null)->toBeString()->not->toBe('')
        ->and($package['scripts']['test:permission-explorer-dom'] ?? null)
        ->toBe('node --test app/zoosper-auth/tests/Browser/permission-explorer-dom.test.js')
        ->and($composer['scripts']['test:permission-explorer-dom'] ?? null)->toBe('npm run test:permission-explorer-dom')
        ->and($workflow)->toContain('Run Permission Explorer DOM behaviour suite')
        ->toContain('run: composer test:permission-explorer-dom')
        ->and($suite)->toBeFile();
});
