<?php

declare(strict_types=1);

it('keeps selected candidate dry-run harness tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/write-method-plugin-selected-candidate-dry-run-harness.php')->toBeFile();
    expect($root . '/tools/audit-method-plugin-selected-candidate-dry-run-harness.php')->toBeFile();
});

it('keeps method plugin runtime disabled by default for dry-run harness planning', function (): void {
    $config = Zoosper\Core\Plugin\MethodPluginRuntimeConfig::disabled();

    expect($config->enabled)->toBeFalse();
    expect($config->reportOnlyInvocationKeys)->toBe([]);
});
