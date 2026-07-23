<?php

declare(strict_types=1);

it('keeps method plugin report-only candidate proof tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/select-method-plugin-report-only-candidate.php')->toBeFile();
    expect($root . '/tools/write-method-plugin-report-only-candidate-plan.php')->toBeFile();
    expect($root . '/tools/audit-method-plugin-report-only-candidate-proof.php')->toBeFile();
});

it('keeps method plugin runtime disabled by default for candidate proof planning', function (): void {
    $config = Zoosper\Core\Plugin\MethodPluginRuntimeConfig::disabled();

    expect($config->enabled)->toBeFalse();
    expect($config->reportOnlyInvocationKeys)->toBe([]);
});
