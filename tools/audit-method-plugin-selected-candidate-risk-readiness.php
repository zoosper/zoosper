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
    'tools/write-method-plugin-selected-candidate-risk-notes.php',
    'tools/write-method-plugin-selected-candidate-rollback-checklist.php',
    'tools/audit-method-plugin-selected-candidate-risk-readiness.php',
    'docs/development/method-plugin-selected-candidate-risk-readiness.md',
];

$report[] = '## Method Plugin Selected Candidate Risk Readiness Audit';
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

$riskExists = is_file($root . '/var/reports/method-plugin-selected-candidate-risk-notes.json');
$rollbackExists = is_file($root . '/var/reports/method-plugin-selected-candidate-rollback-checklist.json');
$config = \Zoosper\Core\Plugin\MethodPluginRuntimeConfig::disabled();

$report[] = '';
$report[] = '- risk notes JSON exists: ' . ($riskExists ? 'yes' : 'no');
$report[] = '- rollback checklist JSON exists: ' . ($rollbackExists ? 'yes' : 'no');
$report[] = '- default runtime enabled: ' . ($config->enabled ? 'yes' : 'no');
$report[] = '- default allow-list count: ' . count($config->reportOnlyInvocationKeys);

if (!$riskExists || !$rollbackExists || $config->enabled || count($config->reportOnlyInvocationKeys) !== 0) {
    $errors++;
}

$report[] = '';
$report[] = 'Production runtime interception enabled: no';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/method-plugin-selected-candidate-risk-readiness-audit.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-selected-candidate-risk-readiness-audit.log', "METHOD_PLUGIN_SELECTED_CANDIDATE_RISK_READINESS_AUDIT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
