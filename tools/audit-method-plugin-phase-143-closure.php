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
$warnings = 0;
$report = [];

$report[] = '## Phase 1.43 Method Plugin Runtime Config Planning Closure Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

$requiredClasses = [
    'Zoosper\\Core\\Plugin\\MethodPluginRuntimeConfig',
    'Zoosper\\Core\\Plugin\\MethodPluginRuntimeConfigLoader',
    'Zoosper\\Core\\Plugin\\MethodPluginRuntimeConfigLayeredLoader',
];

$requiredReports = [
    'var/reports/method-plugin-runtime-config-layering-proof.txt',
    'var/reports/method-plugin-runtime-config-layering-audit.txt',
    'var/reports/method-plugin-selected-candidate-signature.json',
    'var/reports/method-plugin-selected-candidate-fixture-contract-refined.json',
    'var/reports/method-plugin-selected-candidate-signature-readiness-audit.txt',
];

$requiredTools = [
    'tools/prove-method-plugin-runtime-config-layering.php',
    'tools/audit-method-plugin-runtime-config-layering.php',
    'tools/discover-method-plugin-selected-candidate-signature.php',
    'tools/refine-method-plugin-selected-candidate-fixture-contract.php',
    'tools/audit-method-plugin-selected-candidate-signature-readiness.php',
    'tools/audit-bootstrap-config-drift.php',
    'tools/audit-method-plugin-phase-143-closure.php',
];

$report[] = '### Required classes';
foreach ($requiredClasses as $class) {
    $exists = class_exists($class);
    $report[] = '- ' . $class . ': ' . ($exists ? 'yes' : 'no');
    if (!$exists) {
        $errors++;
    }
}

$report[] = '';
$report[] = '### Required reports';
foreach ($requiredReports as $file) {
    $exists = is_file($root . '/' . $file);
    $report[] = '- ' . $file . ': ' . ($exists ? 'exists' : 'missing');
    if (!$exists) {
        $errors++;
    }
}

$report[] = '';
$report[] = '### Required tools';
foreach ($requiredTools as $file) {
    $exists = is_file($root . '/' . $file);
    $report[] = '- ' . $file . ': ' . ($exists ? 'exists' : 'missing');
    if (!$exists) {
        $errors++;
    }
}

$config = \Zoosper\Core\Plugin\MethodPluginRuntimeConfig::disabled();
$report[] = '';
$report[] = '### Runtime disabled-by-default closure guard';
$report[] = '- default runtime enabled: ' . ($config->enabled ? 'yes' : 'no');
$report[] = '- default report-only: ' . ($config->reportOnly ? 'yes' : 'no');
$report[] = '- default allow-list count: ' . count($config->reportOnlyInvocationKeys);
if ($config->enabled || !$config->reportOnly || count($config->reportOnlyInvocationKeys) !== 0) {
    $errors++;
}

$refined = $root . '/var/reports/method-plugin-selected-candidate-fixture-contract-refined.json';
if (is_file($refined)) {
    $contract = json_decode((string) file_get_contents($refined), true);
    $report[] = '';
    $report[] = '### Selected candidate refined fixture guard';
    if (!is_array($contract)) {
        $report[] = '- refined contract readable: no';
        $errors++;
    } else {
        $report[] = '- invocation key: ' . ($contract['invocationKey'] ?? 'missing');
        $report[] = '- fixture only: ' . (($contract['fixtureOnly'] ?? false) ? 'yes' : 'no');
        $report[] = '- service invoked: ' . (($contract['serviceInvoked'] ?? true) ? 'yes' : 'no');
        $report[] = '- production invocation enabled: ' . (($contract['productionInvocationEnabled'] ?? true) ? 'yes' : 'no');
        $report[] = '- output policy: ' . ($contract['outputPolicy']['returnToCaller'] ?? 'missing');
        if (($contract['fixtureOnly'] ?? false) !== true || ($contract['serviceInvoked'] ?? true) !== false || ($contract['productionInvocationEnabled'] ?? true) !== false || ($contract['outputPolicy']['returnToCaller'] ?? '') !== 'baseline-result-only') {
            $errors++;
        }
    }
}

$report[] = '';
$report[] = 'Selected service invoked: no';
$report[] = 'Production runtime interception enabled: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/method-plugin-phase-143-closure.txt', implode("\n", $report) . "\n");
file_put_contents(
    $reportDir . '/method-plugin-phase-143-closure.log',
    "METHOD_PLUGIN_PHASE_143_CLOSURE_WARNINGS {$warnings}\n" .
    "METHOD_PLUGIN_PHASE_143_CLOSURE_ERRORS {$errors}\n"
);

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
