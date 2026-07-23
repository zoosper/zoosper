<?php

declare(strict_types=1);

use Zoosper\Core\Plugin\InMemoryMethodPluginReportSink;
use Zoosper\Core\Plugin\MethodInterceptorInterface;
use Zoosper\Core\Plugin\MethodInvocation;
use Zoosper\Core\Plugin\MethodPluginDefinition;
use Zoosper\Core\Plugin\MethodPluginExecutor;
use Zoosper\Core\Plugin\MethodPluginRegistry;
use Zoosper\Core\Plugin\MethodPluginRuntime;
use Zoosper\Core\Plugin\MethodPluginRuntimeConfig;
use Zoosper\Core\Plugin\ReportOnlyMethodPluginExecutor;

final class Phase141RuntimeSeamSampleService
{
    public function render(string $value): string
    {
        return 'service:' . $value;
    }
}

final class Phase141RuntimeSeamPlugin implements MethodInterceptorInterface
{
    public function intercept(MethodInvocation $invocation, callable $next): mixed
    {
        $arguments = $invocation->arguments;
        $arguments[0] = 'runtime-' . $arguments[0];

        return $next($invocation->withArguments($arguments)) . ':runtime';
    }
}

it('returns baseline output and records no report when runtime seam is disabled', function (): void {
    $sink = new InMemoryMethodPluginReportSink();
    $registry = new MethodPluginRegistry([
        new MethodPluginDefinition(Phase141RuntimeSeamSampleService::class, 'render', Phase141RuntimeSeamPlugin::class, 10),
    ]);
    $runtime = new MethodPluginRuntime(
        MethodPluginRuntimeConfig::disabled(),
        new ReportOnlyMethodPluginExecutor(new MethodPluginExecutor($registry), $sink, [Phase141RuntimeSeamSampleService::class . '::render']),
    );
    $service = new Phase141RuntimeSeamSampleService();

    $result = $runtime->execute(
        new MethodInvocation(Phase141RuntimeSeamSampleService::class, 'render', ['value']),
        static fn (MethodInvocation $invocation): string => $service->render($invocation->arguments[0])
    );

    expect($result)->toBe('service:value');
    expect($sink->results())->toBe([]);
});

it('runs report-only execution for explicit sample allow-list entries only', function (): void {
    $sink = new InMemoryMethodPluginReportSink();
    $registry = new MethodPluginRegistry([
        new MethodPluginDefinition(Phase141RuntimeSeamSampleService::class, 'render', Phase141RuntimeSeamPlugin::class, 10),
    ]);
    $runtime = new MethodPluginRuntime(
        MethodPluginRuntimeConfig::reportOnly([Phase141RuntimeSeamSampleService::class . '::render']),
        new ReportOnlyMethodPluginExecutor(new MethodPluginExecutor($registry), $sink, [Phase141RuntimeSeamSampleService::class . '::render']),
    );
    $service = new Phase141RuntimeSeamSampleService();

    $result = $runtime->execute(
        new MethodInvocation(Phase141RuntimeSeamSampleService::class, 'render', ['value']),
        static fn (MethodInvocation $invocation): string => $service->render($invocation->arguments[0])
    );

    expect($result)->toBe('service:value');
    expect($sink->results())->toHaveCount(1);
    expect($sink->results()[0]->enabled)->toBeTrue();
    expect($sink->results()[0]->baselineResult)->toBe('service:value');
    expect($sink->results()[0]->pluginResult)->toBe('service:runtime-value:runtime');
});

it('keeps runtime seam proof and audit tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/prove-method-plugin-runtime-seam.php')->toBeFile();
    expect($root . '/tools/audit-method-plugin-runtime-seam.php')->toBeFile();
});
