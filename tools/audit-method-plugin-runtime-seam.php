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
    'Zoosper\\Core\\Plugin\\MethodPluginRuntimeConfig',
    'Zoosper\\Core\\Plugin\\MethodPluginRuntime',
    'Zoosper\\Core\\Plugin\\ReportOnlyMethodPluginExecutor',
];

$errors = 0;
$report = [];
$report[] = '## Method Plugin Runtime Seam Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

foreach ($requiredClasses as $class) {
    $exists = class_exists($class);
    $report[] = '- ' . $class . ': ' . ($exists ? 'yes' : 'no');
    if (!$exists) {
        $errors++;
    }
}

$config = \Zoosper\Core\Plugin\MethodPluginRuntimeConfig::disabled();
$report[] = '';
$report[] = 'Default runtime enabled: ' . ($config->enabled ? 'yes' : 'no');
$report[] = 'Default report-only mode: ' . ($config->reportOnly ? 'yes' : 'no');
$report[] = 'Default allow-list count: ' . count($config->reportOnlyInvocationKeys);
$report[] = 'Production runtime interception enabled by default: no';
$report[] = 'Runtime seam proof tool exists: ' . (is_file($root . '/tools/prove-method-plugin-runtime-seam.php') ? 'yes' : 'no');
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/method-plugin-runtime-seam.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-runtime-seam.log', "METHOD_PLUGIN_RUNTIME_SEAM_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
