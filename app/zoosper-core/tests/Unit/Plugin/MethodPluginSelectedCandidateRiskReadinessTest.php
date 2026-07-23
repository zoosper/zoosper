<?php

declare(strict_types=1);

it('keeps selected candidate risk readiness tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/write-method-plugin-selected-candidate-risk-notes.php')->toBeFile();
    expect($root . '/tools/write-method-plugin-selected-candidate-rollback-checklist.php')->toBeFile();
    expect($root . '/tools/audit-method-plugin-selected-candidate-risk-readiness.php')->toBeFile();
});

it('keeps runtime disabled by default for candidate risk readiness', function (): void {
    $config = Zoosper\Core\Plugin\MethodPluginRuntimeConfig::disabled();

    expect($config->enabled)->toBeFalse();
    expect($config->reportOnlyInvocationKeys)->toBe([]);
});
