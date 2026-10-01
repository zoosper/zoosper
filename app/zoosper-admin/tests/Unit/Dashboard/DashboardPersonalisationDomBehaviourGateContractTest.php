<?php

declare(strict_types=1);

it('gates canonical Dashboard personalisation DOM behaviour in npm Composer and CI', function (): void {
    $root = dirname(__DIR__, 5);
    $package = json_decode((string) file_get_contents($root . '/package.json'), true, 512, JSON_THROW_ON_ERROR);
    $composer = json_decode((string) file_get_contents($root . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $workflow = (string) file_get_contents($root . '/.github/workflows/quality-gate.yml');
    $suite = $root . '/app/zoosper-admin/tests/Browser/dashboard-personalisation-dom.test.js';

    expect($package['devDependencies']['jsdom'] ?? null)->toBeString()->not->toBe('')
        ->and($package['scripts']['test:dashboard-dom'] ?? null)
        ->toBe('node --test app/zoosper-admin/tests/Browser/dashboard-personalisation-dom.test.js')
        ->and($composer['scripts']['test:dashboard-dom'] ?? null)->toBe('npm run test:dashboard-dom')
        ->and($workflow)->toContain('Run Dashboard personalisation DOM behaviour suite')
        ->toContain('run: composer test:dashboard-dom')
        ->and($suite)->toBeFile();
});
