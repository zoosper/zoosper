<?php

declare(strict_types=1);

it('keeps selected candidate signature readiness tooling available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/discover-method-plugin-selected-candidate-signature.php')->toBeFile();
    expect($root . '/tools/refine-method-plugin-selected-candidate-fixture-contract.php')->toBeFile();
    expect($root . '/tools/audit-method-plugin-selected-candidate-signature-readiness.php')->toBeFile();
});

it('keeps method plugin runtime disabled by default during signature planning', function (): void {
    $config = Zoosper\Core\Plugin\MethodPluginRuntimeConfig::disabled();

    expect($config->enabled)->toBeFalse();
    expect($config->reportOnlyInvocationKeys)->toBe([]);
});
