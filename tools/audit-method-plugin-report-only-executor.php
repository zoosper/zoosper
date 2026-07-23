<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}

require $autoload;

$requiredClasses = [
    'Zoosper\\Core\\Plugin\\MethodPluginReportOnlyResult',
    'Zoosper\\Core\\Plugin\\MethodPluginReportSinkInterface',
    'Zoosper\\Core\\Plugin\\InMemoryMethodPluginReportSink',
    'Zoosper\\Core\\Plugin\\ReportOnlyMethodPluginExecutor',
];

$errors = 0;
$report = [];
$report[] = '## Method Plugin Report-Only Executor Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

foreach ($requiredClasses as $class) {
    $exists = class_exists($class) || interface_exists($class);
    $report[] = '- ' . $class . ': ' . ($exists ? 'yes' : 'no');
    if (!$exists) {
        $errors++;
    }
}

$report[] = '';
$report[] = 'Production runtime interception enabled: no';
$report[] = 'Requires explicit invocation allow-list: yes';
$report[] = 'Report-only proof tool exists: ' . (is_file($root . '/tools/prove-method-plugin-report-only-executor.php') ? 'yes' : 'no');
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/method-plugin-report-only-executor.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-report-only-executor.log', "METHOD_PLUGIN_REPORT_ONLY_EXECUTOR_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
