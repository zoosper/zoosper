<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminRouteMenuHook;

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
$runtimeConfigPath = $root . '/app/zoosper-page/config/admin_page_momentum_runtime_aggregation_candidate.php';
$hookCandidatePath = $root . '/app/zoosper-page/config/admin_page_momentum_hook_candidate.php';

$report[] = '## Page Momentum Route/Menu Hook Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAdminRouteMenuHook::class)) {
    $report[] = 'Route/menu hook autoloadable: no';
    $errors++;
} elseif (!is_file($runtimeConfigPath) || !is_file($hookCandidatePath)) {
    $report[] = 'Runtime config or hook candidate config missing.';
    $errors++;
} else {
    $runtimeConfig = require $runtimeConfigPath;
    $hookCandidate = require $hookCandidatePath;
    $export = (new PageMomentumAdminRouteMenuHook())->export(
        is_array($runtimeConfig) ? $runtimeConfig : [],
        is_array($hookCandidate) ? $hookCandidate : [],
    );

    $valid = $export['routeCount'] === 1
        && $export['menuCount'] === 1
        && ($export['routes'][0]['name'] ?? '') === 'admin.page_momentum.index'
        && ($export['routes'][0]['path'] ?? '') === '/admin/page-momentum'
        && ($export['routes'][0]['permission'] ?? '') === 'page.manage'
        && ($export['menuItems'][0]['route'] ?? '') === 'admin.page_momentum.index'
        && $export['liveMutation'] === false;

    $report[] = 'Route/menu hook autoloadable: yes';
    $report[] = 'Route count: ' . $export['routeCount'];
    $report[] = 'Menu count: ' . $export['menuCount'];
    $report[] = 'Live mutation performed: ' . ($export['liveMutation'] ? 'yes' : 'no');
    $report[] = 'Route/menu hook valid: ' . ($valid ? 'yes' : 'no');

    if (!$valid) {
        $errors++;
    }

    $reportDir = $root . '/var/reports';
    if (!is_dir($reportDir)) {
        mkdir($reportDir, 0775, true);
    }
    file_put_contents($reportDir . '/page-admin-momentum-route-menu-hook.json', json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
}

$report[] = '';
$report[] = 'Existing aggregator files overwritten: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-route-menu-hook.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-route-menu-hook.log', "PAGE_ADMIN_MOMENTUM_ROUTE_MENU_HOOK_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_ROUTE_MENU_HOOK_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
