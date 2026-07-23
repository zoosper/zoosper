<?php

declare(strict_types=1);

use Zoosper\Core\Plugin\InMemoryMethodPluginReportSink;
use Zoosper\Core\Plugin\MethodInterceptorInterface;
use Zoosper\Core\Plugin\MethodInvocation;
use Zoosper\Core\Plugin\MethodPluginDefinition;
use Zoosper\Core\Plugin\MethodPluginExecutor;
use Zoosper\Core\Plugin\MethodPluginRegistry;
use Zoosper\Core\Plugin\ReportOnlyMethodPluginExecutor;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}

require $autoload;

final class Phase141ToolReportOnlySampleService
{
    public function render(string $value): string
    {
        return 'service:' . $value;
    }
}

final class Phase141ToolReportOnlyPlugin implements MethodInterceptorInterface
{
    public function intercept(MethodInvocation $invocation, callable $next): mixed
    {
        $arguments = $invocation->arguments;
        $arguments[0] = 'plugin-' . $arguments[0];

        return $next($invocation->withArguments($arguments)) . ':plugin';
    }
}

$errors = 0;
$report = [];
$report[] = '## Method Plugin Report-Only Executor Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

try {
    $registry = new MethodPluginRegistry([
        new MethodPluginDefinition(
            subject: Phase141ToolReportOnlySampleService::class,
            method: 'render',
            pluginClass: Phase141ToolReportOnlyPlugin::class,
            sortOrder: 10,
        ),
    ]);
    $sink = new InMemoryMethodPluginReportSink();
    $executor = new ReportOnlyMethodPluginExecutor(
        new MethodPluginExecutor($registry),
        $sink,
        [Phase141ToolReportOnlySampleService::class . '::render'],
    );
    $service = new Phase141ToolReportOnlySampleService();

    $result = $executor->execute(
        new MethodInvocation(Phase141ToolReportOnlySampleService::class, 'render', ['value']),
        static fn (MethodInvocation $invocation): string => $service->render($invocation->arguments[0])
    );
    $record = $sink->results()[0] ?? null;

    $report[] = 'Returned result: ' . var_export($result, true);
    $report[] = 'Report records: ' . count($sink->results());
    $report[] = 'Report enabled: ' . ($record?->enabled ? 'yes' : 'no');
    $report[] = 'Baseline result: ' . var_export($record?->baselineResult, true);
    $report[] = 'Plugin result: ' . var_export($record?->pluginResult, true);
    $report[] = 'Changed: ' . ($record?->changed() ? 'yes' : 'no');

    $proved = $result === 'service:value'
        && $record !== null
        && $record->enabled
        && $record->baselineResult === 'service:value'
        && $record->pluginResult === 'service:plugin-value:plugin'
        && $record->changed();

    $report[] = 'Report-only proof: ' . ($proved ? 'yes' : 'no');

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
file_put_contents($reportDir . '/method-plugin-report-only-executor-proof.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-report-only-executor-proof.log', "METHOD_PLUGIN_REPORT_ONLY_EXECUTOR_PROOF_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
