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
    'tools/discover-method-plugin-selected-candidate-signature.php',
    'tools/refine-method-plugin-selected-candidate-fixture-contract.php',
    'tools/audit-method-plugin-selected-candidate-signature-readiness.php',
    'docs/development/method-plugin-selected-candidate-signature-readiness.md',
];
$requiredReports = [
    'var/reports/method-plugin-selected-candidate-signature.json',
    'var/reports/method-plugin-selected-candidate-fixture-contract-refined.json',
];

$report[] = '## Method Plugin Selected Candidate Signature Readiness Audit';
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

$refined = $root . '/var/reports/method-plugin-selected-candidate-fixture-contract-refined.json';
if (is_file($refined)) {
    $contract = json_decode((string) file_get_contents($refined), true);
    if (!is_array($contract)) {
        $errors++;
        $report[] = '- refined contract readable: no';
    } else {
        $report[] = '- refined contract fixture only: ' . (($contract['fixtureOnly'] ?? false) ? 'yes' : 'no');
        $report[] = '- refined contract service invoked: ' . (($contract['serviceInvoked'] ?? true) ? 'yes' : 'no');
        $report[] = '- refined contract production invocation enabled: ' . (($contract['productionInvocationEnabled'] ?? true) ? 'yes' : 'no');
        if (($contract['fixtureOnly'] ?? false) !== true || ($contract['serviceInvoked'] ?? true) !== false || ($contract['productionInvocationEnabled'] ?? true) !== false) {
            $errors++;
        }
    }
}

$report[] = '';
$report[] = 'Selected service invoked: no';
$report[] = 'Production runtime interception enabled: no';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/method-plugin-selected-candidate-signature-readiness-audit.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-selected-candidate-signature-readiness-audit.log', "METHOD_PLUGIN_SELECTED_CANDIDATE_SIGNATURE_READINESS_AUDIT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
