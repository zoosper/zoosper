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
    'tools/write-method-plugin-selected-candidate-dry-run-harness.php',
    'tools/audit-method-plugin-selected-candidate-dry-run-harness.php',
    'docs/development/method-plugin-selected-candidate-dry-run-harness.md',
];

$report[] = '## Method Plugin Selected Candidate Dry-Run Harness Audit';
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

$harnessFile = $root . '/var/reports/method-plugin-selected-candidate-dry-run-harness.json';
$harnessExists = is_file($harnessFile);
$config = \Zoosper\Core\Plugin\MethodPluginRuntimeConfig::disabled();
$report[] = '';
$report[] = '- harness JSON exists: ' . ($harnessExists ? 'yes' : 'no');
$report[] = '- default runtime enabled: ' . ($config->enabled ? 'yes' : 'no');
$report[] = '- default allow-list count: ' . count($config->reportOnlyInvocationKeys);

if (!$harnessExists || $config->enabled || count($config->reportOnlyInvocationKeys) !== 0) {
    $errors++;
}

if ($harnessExists) {
    $harness = json_decode((string) file_get_contents($harnessFile), true);
    $productionEnabled = is_array($harness) && ($harness['productionInvocationEnabled'] ?? true) === true;
    $fixtureRequired = is_array($harness) && ($harness['fixtureInputRequired'] ?? false) === true;
    $report[] = '- harness production invocation enabled: ' . ($productionEnabled ? 'yes' : 'no');
    $report[] = '- harness fixture input required: ' . ($fixtureRequired ? 'yes' : 'no');
    if ($productionEnabled || !$fixtureRequired) {
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
file_put_contents($reportDir . '/method-plugin-selected-candidate-dry-run-harness-audit.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-selected-candidate-dry-run-harness-audit.log', "METHOD_PLUGIN_SELECTED_CANDIDATE_DRY_RUN_HARNESS_AUDIT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
