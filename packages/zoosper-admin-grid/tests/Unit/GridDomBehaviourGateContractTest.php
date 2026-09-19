<?php

declare(strict_types=1);

it('gates canonical Admin Grid DOM behaviour in npm Composer and CI', function (): void {
    $root = dirname(__DIR__, 4);
    $package = json_decode((string) file_get_contents($root . '/package.json'), true, 512, JSON_THROW_ON_ERROR);
    $composer = json_decode((string) file_get_contents($root . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $workflow = (string) file_get_contents($root . '/.github/workflows/quality-gate.yml');
    $suite = $root . '/packages/zoosper-admin-grid/tests/Browser/admin-grid-dom.test.js';

    expect($package['devDependencies']['jsdom'] ?? null)->toBeString()->not->toBe('')
        ->and($package['scripts']['test:admin-grid-dom'] ?? null)
        ->toBe('node --test packages/zoosper-admin-grid/tests/Browser/admin-grid-dom.test.js')
        ->and($composer['scripts']['test:admin-grid-dom'] ?? null)->toBe('npm run test:admin-grid-dom')
        ->and($workflow)->toContain('Run Admin Grid DOM behaviour suite')
        ->toContain('run: composer test:admin-grid-dom')
        ->and($suite)->toBeFile();
});
