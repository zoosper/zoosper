<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}

require $autoload;

$errors = 0;
$report = [];
$requiredFiles = [
    'tools/discover-method-plugin-service-candidates.php',
    'tools/plan-method-plugin-report-only-candidates.php',
    'tools/audit-method-plugin-service-candidate-discovery.php',
    'docs/development/method-plugin-service-candidate-discovery.md',
];

$report[] = '## Method Plugin Service Candidate Discovery Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

foreach ($requiredFiles as $file) {
    $exists = is_file($root . '/' . $file);
    $report[] = '- ' . $file . ': ' . ($exists ? 'exists' : 'missing');
    if (!$exists) {
        $errors++;
    }
}

$runtimeConfigClass = 'Zoosper\\Core\\Plugin\\MethodPluginRuntimeConfig';
$runtimeConfigExists = class_exists($runtimeConfigClass);
$report[] = '';
$report[] = '- ' . $runtimeConfigClass . ': ' . ($runtimeConfigExists ? 'yes' : 'no');
if (!$runtimeConfigExists) {
    $errors++;
} else {
    $config = \Zoosper\Core\Plugin\MethodPluginRuntimeConfig::disabled();
    $report[] = '- default runtime enabled: ' . ($config->enabled ? 'yes' : 'no');
    $report[] = '- default allow-list count: ' . count($config->reportOnlyInvocationKeys);
    if ($config->enabled || count($config->reportOnlyInvocationKeys) !== 0) {
        $errors++;
    }
}

$report[] = '';
$report[] = 'Production runtime interception enabled: no';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/method-plugin-service-candidate-discovery-audit.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-service-candidate-discovery-audit.log', "METHOD_PLUGIN_SERVICE_CANDIDATE_DISCOVERY_AUDIT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
