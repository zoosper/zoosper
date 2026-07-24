<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$warnings = 0;
$report = [];

$report[] = '## Page Admin Route/Menu Convention Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

$requiredMomentumFiles = [
    'page module admin momentum route metadata' => 'app/zoosper-page/config/admin_page_momentum_routes.php',
    'page module admin momentum menu metadata' => 'app/zoosper-page/config/admin_page_momentum_menu.php',
    'page momentum view' => 'app/zoosper-page/resources/views/admin/page-momentum.latte',
];

$optionalConventionFiles = [
    'page module routes config' => 'app/zoosper-page/config/routes.php',
    'page module admin routes config' => 'app/zoosper-page/config/admin_routes.php',
    'page module controllers config' => 'app/zoosper-page/config/controllers.php',
    'page module menu config' => 'app/zoosper-page/config/admin_menu.php',
];

$report[] = '### Required momentum artefacts';
foreach ($requiredMomentumFiles as $label => $file) {
    $exists = is_file($root . '/' . $file);
    $report[] = '- ' . $label . ': ' . ($exists ? 'exists' : 'missing') . ' (' . $file . ')';
    if (!$exists) {
        $errors++;
    }
}

$report[] = '';
$report[] = '### Existing convention discovery';
foreach ($optionalConventionFiles as $label => $file) {
    $exists = is_file($root . '/' . $file);
    $report[] = '- ' . $label . ': ' . ($exists ? 'exists' : 'not present') . ' (' . $file . ')';
}

$routeConventionFound = is_file($root . '/app/zoosper-page/config/routes.php')
    || is_file($root . '/app/zoosper-page/config/admin_routes.php')
    || is_file($root . '/app/zoosper-page/config/controllers.php');
$menuConventionFound = is_file($root . '/app/zoosper-page/config/admin_menu.php');

$report[] = '';
$report[] = '- route/controller convention found: ' . ($routeConventionFound ? 'yes' : 'no');
$report[] = '- admin menu convention found: ' . ($menuConventionFound ? 'yes' : 'no');

if (!$routeConventionFound) {
    $warnings++;
    $report[] = '- route convention warning: no known page module route/controller convention file was found; future wiring phase must inspect current runtime routing manually.';
}

if (!$menuConventionFound) {
    $warnings++;
    $report[] = '- menu convention warning: no page module admin menu config was found; future wiring phase must inspect current menu aggregation manually.';
}

$routeMeta = $root . '/app/zoosper-page/config/admin_page_momentum_routes.php';
if (is_file($routeMeta)) {
    $config = require $routeMeta;
    $enabled = (bool) ($config['page_momentum_routes']['enabled'] ?? true);
    $routes = $config['page_momentum_routes']['routes'] ?? [];
    $report[] = '';
    $report[] = '- momentum route metadata enabled: ' . ($enabled ? 'yes' : 'no');
    $report[] = '- momentum route metadata count: ' . (is_array($routes) ? count($routes) : 0);
    if ($enabled || !is_array($routes) || count($routes) === 0) {
        $errors++;
    }
}

$menuMeta = $root . '/app/zoosper-page/config/admin_page_momentum_menu.php';
if (is_file($menuMeta)) {
    $config = require $menuMeta;
    $enabled = (bool) ($config['page_momentum_menu']['enabled'] ?? true);
    $items = $config['page_momentum_menu']['items'] ?? [];
    $report[] = '- momentum menu metadata enabled: ' . ($enabled ? 'yes' : 'no');
    $report[] = '- momentum menu metadata count: ' . (is_array($items) ? count($items) : 0);
    if ($enabled || !is_array($items) || count($items) === 0) {
        $errors++;
    }
}

$report[] = '';
$report[] = 'Runtime route changed: no';
$report[] = 'Admin menu changed: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-route-menu-conventions.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-route-menu-conventions.log', "PAGE_ADMIN_ROUTE_MENU_CONVENTIONS_WARNINGS {$warnings}\nPAGE_ADMIN_ROUTE_MENU_CONVENTIONS_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
