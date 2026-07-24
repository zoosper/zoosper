<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$warnings = 0;
$report = [];
$routeFiles = array_values(array_filter([
    $root . '/app/zoosper-page/config/admin_routes.php',
    $root . '/app/zoosper-page/config/routes.php',
], 'is_file'));
$menuFile = $root . '/app/zoosper-page/config/admin_menu.php';

$report[] = '## Page Momentum Live Aggregation Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

$routeFound = false;
foreach ($routeFiles as $file) {
    $config = require $file;
    if (!is_array($config)) {
        continue;
    }
    $routes = array_is_list($config) ? $config : ($config['routes'] ?? []);
    if (!is_array($routes)) {
        continue;
    }
    foreach ($routes as $route) {
        if (is_array($route) && (($route['name'] ?? '') === 'admin.page_momentum.index' || ($route['path'] ?? '') === '/admin/page-momentum')) {
            $routeFound = true;
        }
    }
    $report[] = '- route file inspected: ' . str_replace($root . '/', '', $file);
}

$menuFound = false;
if (is_file($menuFile)) {
    $config = require $menuFile;
    if (is_array($config)) {
        $items = array_is_list($config) ? $config : ($config['items'] ?? []);
        if (is_array($items)) {
            foreach ($items as $item) {
                if (is_array($item) && ($item['route'] ?? '') === 'admin.page_momentum.index') {
                    $menuFound = true;
                }
            }
        }
    }
    $report[] = '- menu file inspected: app/zoosper-page/config/admin_menu.php';
}

$report[] = '- route registered in page module config: ' . ($routeFound ? 'yes' : 'no');
$report[] = '- menu registered in page module config: ' . ($menuFound ? 'yes' : 'no');

if (!$routeFound) {
    $errors++;
}
if (!$menuFound) {
    $errors++;
}

$report[] = 'Expected route: GET /admin/page-momentum';
$report[] = 'Expected permission: page.manage';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-live-aggregation-audit.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-live-aggregation-audit.log', "PAGE_ADMIN_MOMENTUM_LIVE_AGGREGATION_AUDIT_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_LIVE_AGGREGATION_AUDIT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
