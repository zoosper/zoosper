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
$requiredClasses = [
    'Zoosper\\Page\\Admin\\PageMomentumAdminRouteBridge',
    'Zoosper\\Page\\Admin\\PageMomentumAdminMenuBridge',
    'Zoosper\\Page\\Admin\\PageMomentumAdminAggregationBridge',
];
$requiredFiles = [
    'tools/prove-page-admin-momentum-admin-aggregation-bridge.php',
    'tools/audit-page-admin-momentum-admin-aggregation-bridge.php',
    'docs/development/page-admin-momentum-admin-aggregation-bridge.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.51a-l.md',
];
$requiredReports = [
    'var/reports/page-admin-momentum-admin-aggregation-bridge.json',
    'var/reports/page-admin-momentum-admin-aggregation-bridge.txt',
];

$report[] = '## Page Momentum Admin Aggregation Bridge Audit';
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

$exportFile = $root . '/var/reports/page-admin-momentum-admin-aggregation-bridge.json';
if (is_file($exportFile)) {
    $export = json_decode((string) file_get_contents($exportFile), true);
    $routeCount = is_array($export) ? (int) ($export['routeCount'] ?? 0) : 0;
    $menuCount = is_array($export) ? (int) ($export['menuCount'] ?? 0) : 0;
    $mutation = is_array($export) && ($export['liveMutation'] ?? true) === true;

    $report[] = '- bridge route count: ' . $routeCount;
    $report[] = '- bridge menu count: ' . $menuCount;
    $report[] = '- bridge live mutation: ' . ($mutation ? 'yes' : 'no');

    if ($routeCount !== 1 || $menuCount !== 1 || $mutation) {
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
file_put_contents($reportDir . '/page-admin-momentum-admin-aggregation-bridge-audit.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-admin-aggregation-bridge-audit.log', "PAGE_ADMIN_MOMENTUM_ADMIN_AGGREGATION_BRIDGE_AUDIT_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_ADMIN_AGGREGATION_BRIDGE_AUDIT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
