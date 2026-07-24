<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminRuntimeAggregationProvider;

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
$configPath = $root . '/app/zoosper-page/config/admin_page_momentum_runtime_aggregation_candidate.php';
$hookPath = $root . '/app/zoosper-page/config/admin_page_momentum_hook_candidate.php';

$report[] = '## Page Momentum Runtime Aggregation Candidate Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAdminRuntimeAggregationProvider::class)) {
    $report[] = 'Runtime aggregation provider autoloadable: no';
    $errors++;
} elseif (!is_file($configPath) || !is_file($hookPath)) {
    $report[] = 'Runtime aggregation config or hook candidate config missing.';
    $errors++;
} else {
    $config = require $configPath;
    $hookCandidate = require $hookPath;
    $provided = (new PageMomentumAdminRuntimeAggregationProvider())->provide(
        is_array($config) ? $config : [],
        is_array($hookCandidate) ? $hookCandidate : [],
    );

    $valid = $provided['enabled'] === true
        && $provided['routeCount'] === 1
        && $provided['menuCount'] === 1
        && ($provided['routes'][0]['name'] ?? '') === 'admin.page_momentum.index'
        && ($provided['routes'][0]['path'] ?? '') === '/admin/page-momentum'
        && ($provided['routes'][0]['permission'] ?? '') === 'page.manage'
        && ($provided['menuItems'][0]['route'] ?? '') === 'admin.page_momentum.index'
        && $provided['liveMutation'] === false;

    $report[] = 'Runtime aggregation provider autoloadable: yes';
    $report[] = 'Candidate enabled: ' . ($provided['enabled'] ? 'yes' : 'no');
    $report[] = 'Route count: ' . $provided['routeCount'];
    $report[] = 'Menu count: ' . $provided['menuCount'];
    $report[] = 'Live mutation performed: ' . ($provided['liveMutation'] ? 'yes' : 'no');
    $report[] = 'Runtime aggregation candidate valid: ' . ($valid ? 'yes' : 'no');

    if (!$valid) {
        $errors++;
    }

    $reportDir = $root . '/var/reports';
    if (!is_dir($reportDir)) {
        mkdir($reportDir, 0775, true);
    }
    file_put_contents($reportDir . '/page-admin-momentum-runtime-aggregation-candidate.json', json_encode($provided, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
}

$report[] = '';
$report[] = 'Existing aggregator files overwritten: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-runtime-aggregation-candidate.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-runtime-aggregation-candidate.log', "PAGE_ADMIN_MOMENTUM_RUNTIME_AGGREGATION_CANDIDATE_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_RUNTIME_AGGREGATION_CANDIDATE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
