<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminAggregationBridge;

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
$candidatePath = $root . '/app/zoosper-page/config/admin_page_momentum_runtime_candidate.php';

$report[] = '## Page Momentum Admin Aggregation Bridge Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAdminAggregationBridge::class)) {
    $report[] = 'Aggregation bridge autoloadable: no';
    $errors++;
} elseif (!is_file($candidatePath)) {
    $report[] = 'Candidate config missing. Run tools/apply-page-admin-momentum-aggregator-candidate.php first.';
    $errors++;
} else {
    $candidate = require $candidatePath;
    $export = (new PageMomentumAdminAggregationBridge())->export(is_array($candidate) ? $candidate : []);

    $valid = $export['routeCount'] === 1
        && $export['menuCount'] === 1
        && ($export['routes'][0]['name'] ?? '') === 'admin.page_momentum.index'
        && ($export['menuItems'][0]['route'] ?? '') === 'admin.page_momentum.index'
        && $export['liveMutation'] === false;

    $report[] = 'Aggregation bridge autoloadable: yes';
    $report[] = 'Export route count: ' . $export['routeCount'];
    $report[] = 'Export menu count: ' . $export['menuCount'];
    $report[] = 'Live mutation performed: ' . ($export['liveMutation'] ? 'yes' : 'no');
    $report[] = 'Aggregation bridge valid: ' . ($valid ? 'yes' : 'no');

    if (!$valid) {
        $errors++;
    }

    $reportDir = $root . '/var/reports';
    if (!is_dir($reportDir)) {
        mkdir($reportDir, 0775, true);
    }
    file_put_contents($reportDir . '/page-admin-momentum-admin-aggregation-bridge.json', json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
}

$report[] = '';
$report[] = 'Existing aggregator files overwritten: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-admin-aggregation-bridge.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-admin-aggregation-bridge.log', "PAGE_ADMIN_MOMENTUM_ADMIN_AGGREGATION_BRIDGE_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_ADMIN_AGGREGATION_BRIDGE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
