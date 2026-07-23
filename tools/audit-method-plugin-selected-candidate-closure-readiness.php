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
$requiredReports = [
    'var/reports/method-plugin-selected-report-only-candidate.json',
    'var/reports/method-plugin-selected-candidate-dry-run-harness.json',
    'var/reports/method-plugin-selected-candidate-risk-notes.json',
    'var/reports/method-plugin-selected-candidate-rollback-checklist.json',
    'var/reports/method-plugin-selected-candidate-fixture-contract.json',
    'var/reports/method-plugin-selected-candidate-no-invocation-preflight.json',
];

$report[] = '## Method Plugin Selected Candidate Closure Readiness Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

foreach ($requiredReports as $file) {
    $exists = is_file($root . '/' . $file);
    $report[] = '- ' . $file . ': ' . ($exists ? 'exists' : 'missing');
    if (!$exists) {
        $errors++;
    }
}

$config = \Zoosper\Core\Plugin\MethodPluginRuntimeConfig::disabled();
$report[] = '';
$report[] = '- default runtime enabled: ' . ($config->enabled ? 'yes' : 'no');
$report[] = '- default allow-list count: ' . count($config->reportOnlyInvocationKeys);
if ($config->enabled || count($config->reportOnlyInvocationKeys) !== 0) {
    $errors++;
}

$report[] = '';
$report[] = 'Service invocation performed: no';
$report[] = 'Production runtime interception enabled: no';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/method-plugin-selected-candidate-closure-readiness-audit.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-selected-candidate-closure-readiness-audit.log', "METHOD_PLUGIN_SELECTED_CANDIDATE_CLOSURE_READINESS_AUDIT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
