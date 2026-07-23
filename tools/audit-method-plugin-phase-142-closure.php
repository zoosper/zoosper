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

$report[] = '## Phase 1.42 Method Plugin Opt-In Planning Closure Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

$requiredReports = [
    'var/reports/method-plugin-service-candidates.json',
    'var/reports/method-plugin-selected-report-only-candidate.json',
    'var/reports/method-plugin-selected-candidate-dry-run-harness.json',
    'var/reports/method-plugin-selected-candidate-risk-notes.json',
    'var/reports/method-plugin-selected-candidate-rollback-checklist.json',
    'var/reports/method-plugin-selected-candidate-fixture-contract.json',
    'var/reports/method-plugin-selected-candidate-fixture-contract-validation.txt',
    'var/reports/method-plugin-selected-candidate-no-invocation-preflight.json',
    'var/reports/method-plugin-selected-candidate-closure-readiness-audit.txt',
];

$optionalPlanReportGroups = [
    'general report-only candidate plan' => [
        'var/reports/method-plugin-report-only-candidate-plan.txt',
        'var/reports/method-plugin-report-only-candidates.txt',
    ],
    'selected report-only candidate plan' => [
        'var/reports/method-plugin-selected-report-only-candidate-plan.txt',
        'var/reports/method-plugin-selected-candidate-plan.txt',
    ],
];

$requiredTools = [
    'tools/discover-method-plugin-service-candidates.php',
    'tools/plan-method-plugin-report-only-candidates.php',
    'tools/audit-method-plugin-service-candidate-discovery.php',
    'tools/select-method-plugin-report-only-candidate.php',
    'tools/write-method-plugin-report-only-candidate-plan.php',
    'tools/audit-method-plugin-report-only-candidate-proof.php',
    'tools/write-method-plugin-selected-candidate-dry-run-harness.php',
    'tools/audit-method-plugin-selected-candidate-dry-run-harness.php',
    'tools/write-method-plugin-selected-candidate-risk-notes.php',
    'tools/write-method-plugin-selected-candidate-rollback-checklist.php',
    'tools/audit-method-plugin-selected-candidate-risk-readiness.php',
    'tools/write-method-plugin-selected-candidate-fixture-contract.php',
    'tools/validate-method-plugin-selected-candidate-fixture-contract.php',
    'tools/write-method-plugin-selected-candidate-no-invocation-preflight.php',
    'tools/audit-method-plugin-selected-candidate-closure-readiness.php',
];

$report[] = '### Required reports';
foreach ($requiredReports as $file) {
    $exists = is_file($root . '/' . $file);
    $report[] = '- ' . $file . ': ' . ($exists ? 'exists' : 'missing');
    if (!$exists) {
        $errors++;
    }
}

$report[] = '';
$report[] = '### Optional planning reports';
foreach ($optionalPlanReportGroups as $label => $alternatives) {
    $found = null;
    foreach ($alternatives as $file) {
        if (is_file($root . '/' . $file)) {
            $found = $file;
            break;
        }
    }

    if ($found === null) {
        $report[] = '- ' . $label . ': missing (non-blocking; downstream selected candidate artefacts exist)';
        $warnings++;
    } else {
        $report[] = '- ' . $label . ': exists (' . $found . ')';
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

$contractFile = $root . '/var/reports/method-plugin-selected-candidate-fixture-contract.json';
if (is_file($contractFile)) {
    $contract = json_decode((string) file_get_contents($contractFile), true);
    $report[] = '';
    $report[] = '### Selected candidate fixture guard';
    if (!is_array($contract)) {
        $report[] = '- contract readable: no';
        $errors++;
    } else {
        $report[] = '- invocation key: ' . ($contract['invocationKey'] ?? 'missing');
        $report[] = '- fixture only: ' . (($contract['fixtureOnly'] ?? false) ? 'yes' : 'no');
        $report[] = '- production invocation enabled: ' . (($contract['productionInvocationEnabled'] ?? true) ? 'yes' : 'no');
        $report[] = '- output policy: ' . ($contract['outputPolicy']['returnToCaller'] ?? 'missing');
        if (($contract['fixtureOnly'] ?? false) !== true || ($contract['productionInvocationEnabled'] ?? true) !== false || ($contract['outputPolicy']['returnToCaller'] ?? '') !== 'baseline-result-only') {
            $errors++;
        }
    }
}

$preflightFile = $root . '/var/reports/method-plugin-selected-candidate-no-invocation-preflight.json';
if (is_file($preflightFile)) {
    $preflight = json_decode((string) file_get_contents($preflightFile), true);
    $report[] = '';
    $report[] = '### No-invocation preflight guard';
    if (!is_array($preflight)) {
        $report[] = '- preflight readable: no';
        $errors++;
    } else {
        $report[] = '- service invocation performed: ' . (($preflight['serviceInvocationPerformed'] ?? true) ? 'yes' : 'no');
        $report[] = '- runtime config changed: ' . (($preflight['productionRuntimeConfigChanged'] ?? true) ? 'yes' : 'no');
        $report[] = '- allow-list changed: ' . (($preflight['allowListChanged'] ?? true) ? 'yes' : 'no');
        if (($preflight['serviceInvocationPerformed'] ?? true) || ($preflight['productionRuntimeConfigChanged'] ?? true) || ($preflight['allowListChanged'] ?? true)) {
            $errors++;
        }
    }
}

$report[] = '';
$report[] = 'Service invocation performed: no';
$report[] = 'Production runtime interception enabled: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/method-plugin-phase-142-closure.txt', implode("\n", $report) . "\n");
file_put_contents(
    $reportDir . '/method-plugin-phase-142-closure.log',
    "METHOD_PLUGIN_PHASE_142_CLOSURE_WARNINGS {$warnings}\n" .
    "METHOD_PLUGIN_PHASE_142_CLOSURE_ERRORS {$errors}\n"
);

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
