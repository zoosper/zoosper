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
    'tools/select-method-plugin-report-only-candidate.php',
    'tools/write-method-plugin-report-only-candidate-plan.php',
    'tools/audit-method-plugin-report-only-candidate-proof.php',
    'docs/development/method-plugin-report-only-candidate-proof.md',
];

$report[] = '## Method Plugin Report-Only Candidate Proof Audit';
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

$selectedExists = is_file($root . '/var/reports/method-plugin-selected-report-only-candidate.json');
$planExists = is_file($root . '/var/reports/method-plugin-selected-report-only-candidate-plan.txt');
$config = \Zoosper\Core\Plugin\MethodPluginRuntimeConfig::disabled();

$report[] = '';
$report[] = '- selected candidate report exists: ' . ($selectedExists ? 'yes' : 'no');
$report[] = '- selected candidate plan exists: ' . ($planExists ? 'yes' : 'no');
$report[] = '- default runtime enabled: ' . ($config->enabled ? 'yes' : 'no');
$report[] = '- default allow-list count: ' . count($config->reportOnlyInvocationKeys);

if (!$selectedExists || !$planExists || $config->enabled || count($config->reportOnlyInvocationKeys) !== 0) {
    $errors++;
}

$report[] = '';
$report[] = 'Production runtime interception enabled: no';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/method-plugin-report-only-candidate-proof-audit.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-report-only-candidate-proof-audit.log', "METHOD_PLUGIN_REPORT_ONLY_CANDIDATE_PROOF_AUDIT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
