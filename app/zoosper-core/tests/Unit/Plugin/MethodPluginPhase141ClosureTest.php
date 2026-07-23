<?php

declare(strict_types=1);

it('keeps all Phase 1.41 method plugin foundation classes available', function (): void {
    $classes = [
        Zoosper\Core\Plugin\MethodInvocation::class,
        Zoosper\Core\Plugin\MethodInterceptorInterface::class,
        Zoosper\Core\Plugin\MethodInterceptorChain::class,
        Zoosper\Core\Plugin\MethodPluginRegistry::class,
        Zoosper\Core\Plugin\MethodPluginExecutor::class,
        Zoosper\Core\Plugin\MethodPluginConfigSourceDiscovery::class,
        Zoosper\Core\Plugin\MethodPluginResolverInterface::class,
        Zoosper\Core\Plugin\MethodPluginConfigValidator::class,
        Zoosper\Core\Plugin\ReportOnlyMethodPluginExecutor::class,
        Zoosper\Core\Plugin\MethodPluginRuntimeConfig::class,
        Zoosper\Core\Plugin\MethodPluginRuntime::class,
    ];

    foreach ($classes as $class) {
        expect(class_exists($class) || interface_exists($class))->toBeTrue($class . ' should exist');
    }
});

it('keeps method plugin runtime disabled by default', function (): void {
    $config = Zoosper\Core\Plugin\MethodPluginRuntimeConfig::disabled();

    expect($config->enabled)->toBeFalse();
    expect($config->reportOnly)->toBeTrue();
    expect($config->reportOnlyInvocationKeys)->toBe([]);
});

it('keeps the Phase 1.41 closure audit tool available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/audit-method-plugin-phase-141-closure.php')->toBeFile();
});
