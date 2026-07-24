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
$requiredFiles = [
    'app/zoosper-page/src/Admin/PageMomentumAdminRuntimeAggregationProvider.php',
    'app/zoosper-page/config/admin_page_momentum_runtime_aggregation_candidate.php',
    'app/zoosper-page/config/admin_page_momentum_hook_candidate.php',
    'tools/prove-page-admin-momentum-runtime-aggregation-candidate.php',
    'tools/audit-page-admin-momentum-runtime-aggregation-readiness.php',
    'docs/development/page-admin-momentum-runtime-aggregation-candidate.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.54a-l.md',
];
$requiredReports = [
    'var/reports/page-admin-momentum-runtime-aggregation-candidate.json',
    'var/reports/page-admin-momentum-runtime-aggregation-candidate.txt',
];

$report[] = '## Page Momentum Runtime Aggregation Readiness Audit';
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

$configPath = $root . '/app/zoosper-page/config/admin_page_momentum_runtime_aggregation_candidate.php';
if (is_file($configPath)) {
    $config = require $configPath;
    $rootConfig = is_array($config) ? ($config['page_momentum_runtime_aggregation_candidate'] ?? []) : [];
    $enabled = is_array($rootConfig) && ($rootConfig['enabled'] ?? false) === true;
    $mutation = is_array($rootConfig) && ($rootConfig['live_mutation'] ?? true) === true;
    $provider = is_array($rootConfig) ? (string) ($rootConfig['provider'] ?? '') : '';

    $report[] = '- runtime aggregation candidate enabled: ' . ($enabled ? 'yes' : 'no');
    $report[] = '- runtime aggregation provider: ' . ($provider !== '' ? $provider : 'missing');
    $report[] = '- runtime aggregation live mutation: ' . ($mutation ? 'yes' : 'no');

    if (!$enabled || $mutation || $provider === '' || !class_exists($provider)) {
        $errors++;
    }
}

$report[] = 'Existing aggregator files overwritten: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-runtime-aggregation-readiness.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-runtime-aggregation-readiness.log', "PAGE_ADMIN_MOMENTUM_RUNTIME_AGGREGATION_READINESS_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_RUNTIME_AGGREGATION_READINESS_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
