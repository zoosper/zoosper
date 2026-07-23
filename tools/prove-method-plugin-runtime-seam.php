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

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}

require $autoload;

final class Phase141ToolRuntimeSeamSampleService
{
    public function render(string $value): string
    {
        return 'service:' . $value;
    }
}

final class Phase141ToolRuntimeSeamPlugin implements MethodInterceptorInterface
{
    public function intercept(MethodInvocation $invocation, callable $next): mixed
    {
        $arguments = $invocation->arguments;
        $arguments[0] = 'runtime-' . $arguments[0];

        return $next($invocation->withArguments($arguments)) . ':runtime';
    }
}

$errors = 0;
$report = [];
$report[] = '## Method Plugin Runtime Seam Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

try {
    $registry = new MethodPluginRegistry([
        new MethodPluginDefinition(Phase141ToolRuntimeSeamSampleService::class, 'render', Phase141ToolRuntimeSeamPlugin::class, 10),
    ]);

    $disabledSink = new InMemoryMethodPluginReportSink();
    $disabledRuntime = new MethodPluginRuntime(
        MethodPluginRuntimeConfig::disabled(),
        new ReportOnlyMethodPluginExecutor(new MethodPluginExecutor($registry), $disabledSink, [Phase141ToolRuntimeSeamSampleService::class . '::render']),
    );
    $service = new Phase141ToolRuntimeSeamSampleService();
    $disabledResult = $disabledRuntime->execute(
        new MethodInvocation(Phase141ToolRuntimeSeamSampleService::class, 'render', ['value']),
        static fn (MethodInvocation $invocation): string => $service->render($invocation->arguments[0])
    );

    $enabledSink = new InMemoryMethodPluginReportSink();
    $enabledRuntime = new MethodPluginRuntime(
        MethodPluginRuntimeConfig::reportOnly([Phase141ToolRuntimeSeamSampleService::class . '::render']),
        new ReportOnlyMethodPluginExecutor(new MethodPluginExecutor($registry), $enabledSink, [Phase141ToolRuntimeSeamSampleService::class . '::render']),
    );
    $enabledResult = $enabledRuntime->execute(
        new MethodInvocation(Phase141ToolRuntimeSeamSampleService::class, 'render', ['value']),
        static fn (MethodInvocation $invocation): string => $service->render($invocation->arguments[0])
    );
    $record = $enabledSink->results()[0] ?? null;

    $report[] = 'Disabled returned baseline: ' . ($disabledResult === 'service:value' ? 'yes' : 'no');
    $report[] = 'Disabled reports: ' . count($disabledSink->results());
    $report[] = 'Enabled returned baseline: ' . ($enabledResult === 'service:value' ? 'yes' : 'no');
    $report[] = 'Enabled reports: ' . count($enabledSink->results());
    $report[] = 'Enabled plugin result: ' . var_export($record?->pluginResult, true);

    $proved = $disabledResult === 'service:value'
        && count($disabledSink->results()) === 0
        && $enabledResult === 'service:value'
        && $record !== null
        && $record->enabled
        && $record->pluginResult === 'service:runtime-value:runtime';

    $report[] = 'Runtime seam proof: ' . ($proved ? 'yes' : 'no');

    if (!$proved) {
        $errors++;
    }
} catch (Throwable $exception) {
    $report[] = 'Exception: ' . $exception->getMessage();
    $errors++;
}

$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/method-plugin-runtime-seam-proof.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-runtime-seam-proof.log', "METHOD_PLUGIN_RUNTIME_SEAM_PROOF_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
