<?php

declare(strict_types=1);

it('migrates and checks the MySQL production-target database before release', function (): void {
    $root = dirname(__DIR__, 5);
    $workflow = (string) file_get_contents($root . '/.github/workflows/quality-gate.yml');

    $prepare = strpos($workflow, 'name: Prepare CI runtime');
    $migrate = strpos($workflow, 'name: Migrate CI release database');
    $release = strpos($workflow, 'name: Run alpha release checks');
    $freshInstall = strpos($workflow, 'name: Prove disposable fresh install and critical runtime inventory');

    expect($prepare)->not->toBeFalse()
        ->and($migrate)->not->toBeFalse()
        ->and($release)->not->toBeFalse()
        ->and($freshInstall)->not->toBeFalse()
        ->and($prepare)->toBeLessThan($migrate)
        ->and($migrate)->toBeLessThan($release)
        ->and($release)->toBeLessThan($freshInstall)
        ->and($workflow)->toContain('run: composer migrate')
        ->and($workflow)->toContain('run: composer release:check')
        ->and(substr_count($workflow, 'DB_CONNECTION: mysql'))->toBeGreaterThanOrEqual(3)
        ->and(substr_count($workflow, 'DB_DATABASE: zoosper_test'))->toBeGreaterThanOrEqual(3);
});
