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
    'app/zoosper-page/src/Admin/PageMomentumDefinitionProvider.php',
    'app/zoosper-page/config/admin_page_momentum_routes.php',
    'app/zoosper-page/config/admin_page_momentum_menu.php',
    'tools/prove-page-admin-momentum-controller.php',
    'tools/prove-page-admin-momentum-definition-provider.php',
    'tools/audit-page-admin-momentum-runtime-bridge-readiness.php',
    'docs/development/page-admin-momentum-runtime-bridge-readiness.md',
];

$report[] = '## Page Admin Momentum Runtime Bridge Readiness Audit';
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

$symbols = [
    'Zoosper\\Page\\Admin\\Controller\\PageMomentumAdminController',
    'Zoosper\\Page\\Admin\\PageMomentumDefinitionProvider',
];
foreach ($symbols as $symbol) {
    $exists = class_exists($symbol);
    $report[] = '- ' . $symbol . ': ' . ($exists ? 'yes' : 'no');
    if (!$exists) {
        $errors++;
    }
}

$routeConfig = is_file($root . '/app/zoosper-page/config/admin_page_momentum_routes.php')
    ? require $root . '/app/zoosper-page/config/admin_page_momentum_routes.php'
    : [];
$menuConfig = is_file($root . '/app/zoosper-page/config/admin_page_momentum_menu.php')
    ? require $root . '/app/zoosper-page/config/admin_page_momentum_menu.php'
    : [];

$routeEnabled = (bool) ($routeConfig['page_momentum_routes']['enabled'] ?? true);
$menuEnabled = (bool) ($menuConfig['page_momentum_menu']['enabled'] ?? true);

$report[] = '';
$report[] = '- route metadata enabled: ' . ($routeEnabled ? 'yes' : 'no');
$report[] = '- menu metadata enabled: ' . ($menuEnabled ? 'yes' : 'no');

if ($routeEnabled || $menuEnabled) {
    $errors++;
}

$report[] = 'Runtime route registered: no';
$report[] = 'Admin menu enabled: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-runtime-bridge-readiness.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-runtime-bridge-readiness.log', "PAGE_ADMIN_MOMENTUM_RUNTIME_BRIDGE_READINESS_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_RUNTIME_BRIDGE_READINESS_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
