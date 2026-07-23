<?php

declare(strict_types=1);

it('keeps method plugin service candidate discovery tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/discover-method-plugin-service-candidates.php')->toBeFile();
    expect($root . '/tools/plan-method-plugin-report-only-candidates.php')->toBeFile();
    expect($root . '/tools/audit-method-plugin-service-candidate-discovery.php')->toBeFile();
});

it('keeps method plugin runtime disabled by default while discovering candidates', function (): void {
    $config = Zoosper\Core\Plugin\MethodPluginRuntimeConfig::disabled();

    expect($config->enabled)->toBeFalse();
    expect($config->reportOnly)->toBeTrue();
    expect($config->reportOnlyInvocationKeys)->toBe([]);
});
