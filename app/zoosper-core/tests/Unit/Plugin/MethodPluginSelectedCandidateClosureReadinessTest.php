<?php

declare(strict_types=1);

it('keeps selected candidate fixture closure readiness tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/write-method-plugin-selected-candidate-fixture-contract.php')->toBeFile();
    expect($root . '/tools/validate-method-plugin-selected-candidate-fixture-contract.php')->toBeFile();
    expect($root . '/tools/write-method-plugin-selected-candidate-no-invocation-preflight.php')->toBeFile();
    expect($root . '/tools/audit-method-plugin-selected-candidate-closure-readiness.php')->toBeFile();
});

it('keeps runtime disabled by default for selected candidate closure readiness', function (): void {
    $config = Zoosper\Core\Plugin\MethodPluginRuntimeConfig::disabled();

    expect($config->enabled)->toBeFalse();
    expect($config->reportOnlyInvocationKeys)->toBe([]);
});
