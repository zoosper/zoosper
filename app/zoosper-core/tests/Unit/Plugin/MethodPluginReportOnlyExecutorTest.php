<?php

declare(strict_types=1);

use Zoosper\Core\Plugin\InMemoryMethodPluginReportSink;
use Zoosper\Core\Plugin\MethodInterceptorInterface;
use Zoosper\Core\Plugin\MethodInvocation;
use Zoosper\Core\Plugin\MethodPluginDefinition;
use Zoosper\Core\Plugin\MethodPluginExecutor;
use Zoosper\Core\Plugin\MethodPluginRegistry;
use Zoosper\Core\Plugin\ReportOnlyMethodPluginExecutor;

final class Phase141ReportOnlySampleService
{
    public function render(string $value): string
    {
        return 'service:' . $value;
    }
}

final class Phase141ReportOnlyPlugin implements MethodInterceptorInterface
{
    public function intercept(MethodInvocation $invocation, callable $next): mixed
    {
        $arguments = $invocation->arguments;
        $arguments[0] = 'plugin-' . $arguments[0];

        return $next($invocation->withArguments($arguments)) . ':plugin';
    }
}

it('reports plugin output but returns the baseline result for allow-listed sample service calls', function (): void {
    $registry = new MethodPluginRegistry([
        new MethodPluginDefinition(
            subject: Phase141ReportOnlySampleService::class,
            method: 'render',
            pluginClass: Phase141ReportOnlyPlugin::class,
            sortOrder: 10,
        ),
    ]);
    $sink = new InMemoryMethodPluginReportSink();
    $executor = new ReportOnlyMethodPluginExecutor(
        new MethodPluginExecutor($registry),
        $sink,
        [Phase141ReportOnlySampleService::class . '::render'],
    );
    $service = new Phase141ReportOnlySampleService();

    $result = $executor->execute(
        new MethodInvocation(Phase141ReportOnlySampleService::class, 'render', ['value']),
        static fn (MethodInvocation $invocation): string => $service->render($invocation->arguments[0])
    );

    expect($result)->toBe('service:value');
    expect($sink->results())->toHaveCount(1);
    expect($sink->results()[0]->enabled)->toBeTrue();
    expect($sink->results()[0]->baselineResult)->toBe('service:value');
    expect($sink->results()[0]->pluginResult)->toBe('service:plugin-value:plugin');
    expect($sink->results()[0]->changed())->toBeTrue();
});

it('does not execute plugins when invocation is not allow-listed', function (): void {
    $registry = new MethodPluginRegistry([
        new MethodPluginDefinition(
            subject: Phase141ReportOnlySampleService::class,
            method: 'render',
            pluginClass: Phase141ReportOnlyPlugin::class,
            sortOrder: 10,
        ),
    ]);
    $sink = new InMemoryMethodPluginReportSink();
    $executor = new ReportOnlyMethodPluginExecutor(new MethodPluginExecutor($registry), $sink, []);
    $service = new Phase141ReportOnlySampleService();

    $result = $executor->execute(
        new MethodInvocation(Phase141ReportOnlySampleService::class, 'render', ['value']),
        static fn (MethodInvocation $invocation): string => $service->render($invocation->arguments[0])
    );

    expect($result)->toBe('service:value');
    expect($sink->results())->toHaveCount(1);
    expect($sink->results()[0]->enabled)->toBeFalse();
    expect($sink->results()[0]->pluginResult)->toBeNull();
});

it('keeps report-only proof and audit tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/prove-method-plugin-report-only-executor.php')->toBeFile();
    expect($root . '/tools/audit-method-plugin-report-only-executor.php')->toBeFile();
});
