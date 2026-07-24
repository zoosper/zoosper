<?php

declare(strict_types=1);

it('keeps Phase 1.43 closure and bootstrap drift audit tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/audit-bootstrap-config-drift.php')->toBeFile();
    expect($root . '/tools/audit-method-plugin-phase-143-closure.php')->toBeFile();
});

it('keeps method plugin runtime disabled by default at Phase 1.43 closure', function (): void {
    $config = Zoosper\Core\Plugin\MethodPluginRuntimeConfig::disabled();

    expect($config->enabled)->toBeFalse();
    expect($config->reportOnly)->toBeTrue();
    expect($config->reportOnlyInvocationKeys)->toBe([]);
});

it('documents that Phase 1.43 does not enable production interception', function (): void {
    $root = dirname(__DIR__, 5);
    $doc = $root . '/docs/development/method-plugin-phase-1.43-closure.md';

    expect($doc)->toBeFile();
    $contents = (string) file_get_contents($doc);
    expect($contents)->toContain('No production runtime interception is enabled');
    expect($contents)->toContain('No selected service method is invoked');
});
