<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumLiveDuplicateGuard;

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
$routePath = is_file($root . '/app/zoosper-page/config/admin_routes.php')
    ? $root . '/app/zoosper-page/config/admin_routes.php'
    : $root . '/app/zoosper-page/config/routes.php';
$menuPath = $root . '/app/zoosper-page/config/admin_menu.php';

$report[] = '## Page Momentum Live Duplicate Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumLiveDuplicateGuard::class)) {
    $report[] = 'Duplicate guard autoloadable: no';
    $errors++;
} elseif (!is_file($routePath) || !is_file($menuPath)) {
    $report[] = 'Route or menu config missing.';
    $errors++;
} else {
    $routeConfig = require $routePath;
    $menuConfig = require $menuPath;

    if (!is_array($routeConfig) || !is_array($menuConfig)) {
        $report[] = 'Route or menu config did not return arrays.';
        $errors++;
    } else {
        $result = (new PageMomentumLiveDuplicateGuard())->inspect($routeConfig, $menuConfig);
        $report[] = 'Route config inspected: ' . str_replace($root . '/', '', $routePath);
        $report[] = 'Menu config inspected: ' . str_replace($root . '/', '', $menuPath);
        $report[] = 'Route matches: ' . $result['routeMatches'];
        $report[] = 'Menu matches: ' . $result['menuMatches'];
        $report[] = 'Route duplicate guard: ' . ($result['routeOk'] ? 'pass' : 'fail');
        $report[] = 'Menu duplicate guard: ' . ($result['menuOk'] ? 'pass' : 'fail');
        if (!$result['ok']) {
            $errors++;
        }
    }
}

$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-live-duplicates.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-live-duplicates.log', "PAGE_ADMIN_MOMENTUM_LIVE_DUPLICATES_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_LIVE_DUPLICATES_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
