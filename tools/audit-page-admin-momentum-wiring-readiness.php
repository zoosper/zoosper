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
    'app/zoosper-page/src/Admin/Controller/PageMomentumAdminController.php',
    'app/zoosper-page/config/admin_page_momentum.php',
    'app/zoosper-page/config/admin_page_momentum_routes.php',
    'app/zoosper-page/config/admin_page_momentum_menu.php',
    'app/zoosper-page/resources/views/admin/page-momentum.latte',
    'tools/prove-page-admin-momentum-controller.php',
    'tools/audit-page-admin-momentum-wiring-readiness.php',
    'docs/development/page-admin-momentum-wiring-readiness.md',
];

$report[] = '## Page Admin Momentum Wiring Readiness Audit';
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

$controllerClass = 'Zoosper\\Page\\Admin\\Controller\\PageMomentumAdminController';
$report[] = '- ' . $controllerClass . ': ' . (class_exists($controllerClass) ? 'yes' : 'no');
if (!class_exists($controllerClass)) {
    $errors++;
}

$routeConfigPath = $root . '/app/zoosper-page/config/admin_page_momentum_routes.php';
if (is_file($routeConfigPath)) {
    $config = require $routeConfigPath;
    $enabled = (bool) ($config['page_momentum_routes']['enabled'] ?? true);
    $routes = $config['page_momentum_routes']['routes'] ?? [];
    $first = is_array($routes) ? ($routes[0] ?? []) : [];
    $controllerOk = is_array($first) && ($first['controller'] ?? '') === $controllerClass;
    $actionOk = is_array($first) && ($first['action'] ?? '') === 'index';
    $permissionOk = is_array($first) && ($first['permission'] ?? '') === 'page.manage';

    $report[] = '';
    $report[] = '- route metadata enabled: ' . ($enabled ? 'yes' : 'no');
    $report[] = '- route metadata controller ok: ' . ($controllerOk ? 'yes' : 'no');
    $report[] = '- route metadata action ok: ' . ($actionOk ? 'yes' : 'no');
    $report[] = '- route metadata permission ok: ' . ($permissionOk ? 'yes' : 'no');

    if ($enabled || !$controllerOk || !$actionOk || !$permissionOk) {
        $errors++;
    }
}

$menuConfigPath = $root . '/app/zoosper-page/config/admin_page_momentum_menu.php';
if (is_file($menuConfigPath)) {
    $config = require $menuConfigPath;
    $enabled = (bool) ($config['page_momentum_menu']['enabled'] ?? true);
    $items = $config['page_momentum_menu']['items'] ?? [];
    $first = is_array($items) ? ($items[0] ?? []) : [];
    $routeOk = is_array($first) && ($first['route'] ?? '') === 'admin.page_momentum.index';
    $permissionOk = is_array($first) && ($first['permission'] ?? '') === 'page.manage';

    $report[] = '- menu metadata enabled: ' . ($enabled ? 'yes' : 'no');
    $report[] = '- menu metadata route ok: ' . ($routeOk ? 'yes' : 'no');
    $report[] = '- menu metadata permission ok: ' . ($permissionOk ? 'yes' : 'no');

    if ($enabled || !$routeOk || !$permissionOk) {
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
file_put_contents($reportDir . '/page-admin-momentum-wiring-readiness.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-wiring-readiness.log', "PAGE_ADMIN_MOMENTUM_WIRING_READINESS_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_WIRING_READINESS_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
