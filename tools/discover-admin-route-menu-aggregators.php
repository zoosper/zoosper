<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$warnings = 0;
$report = [];

$report[] = '## Admin Route/Menu Aggregator Discovery';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

$globSets = [
    'routeFiles' => [
        'app/*/config/routes.php',
        'app/*/config/admin_routes.php',
        'app/*/config/controllers.php',
        'app/*/config/admin_controllers.php',
    ],
    'menuFiles' => [
        'app/*/config/admin_menu.php',
        'app/*/config/menu.php',
    ],
    'controllerFiles' => [
        'app/*/src/Admin/Controller/*.php',
        'app/*/src/Controller/Admin/*.php',
    ],
];

$discovery = [];
foreach ($globSets as $key => $patterns) {
    $files = [];
    foreach ($patterns as $pattern) {
        foreach (glob($root . '/' . $pattern) ?: [] as $file) {
            $files[] = str_replace($root . '/', '', $file);
        }
    }
    $files = array_values(array_unique($files));
    sort($files);
    $discovery[$key] = $files;
}

foreach ($discovery as $key => $files) {
    $report[] = '### ' . $key;
    $report[] = 'Count: ' . count($files);
    foreach ($files as $file) {
        $report[] = '- ' . $file;
    }
    $report[] = '';
}

if (count($discovery['menuFiles'] ?? []) === 0) {
    $warnings++;
    $report[] = 'WARNING: no admin menu convention files discovered.';
}
if (count($discovery['routeFiles'] ?? []) === 0 && count($discovery['controllerFiles'] ?? []) === 0) {
    $warnings++;
    $report[] = 'WARNING: no route/controller convention files discovered.';
}

$report[] = 'Live mutation performed: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/admin-route-menu-aggregator-discovery.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/admin-route-menu-aggregator-discovery.json', json_encode($discovery, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
file_put_contents($reportDir . '/admin-route-menu-aggregator-discovery.log', "ADMIN_ROUTE_MENU_AGGREGATOR_DISCOVERY_WARNINGS {$warnings}\nADMIN_ROUTE_MENU_AGGREGATOR_DISCOVERY_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
