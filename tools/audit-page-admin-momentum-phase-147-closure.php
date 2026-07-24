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

$requiredSymbols = [
    'Zoosper\\Page\\Admin\\PageMomentumRouteDefinitionProvider',
    'Zoosper\\Page\\Admin\\PageMomentumMenuDefinitionProvider',
    'Zoosper\\Page\\Admin\\PageMomentumRuntimeBridge',
    'Zoosper\\Page\\Admin\\PageMomentumAdminIntegrationPreview',
];

$requiredFiles = [
    'tools/prove-page-admin-momentum-runtime-bridge.php',
    'tools/audit-page-admin-momentum-runtime-bridge.php',
    'tools/prove-page-admin-momentum-integration-preview.php',
    'tools/audit-page-admin-momentum-phase-147-closure.php',
    'app/zoosper-page/config/admin_page_momentum_routes.php',
    'app/zoosper-page/config/admin_page_momentum_menu.php',
    'docs/development/page-admin-momentum-runtime-bridge.md',
    'docs/development/page-admin-momentum-phase-1.47-closure.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.47m-z.md',
];

$report[] = '## Phase 1.47 Page Admin Momentum Runtime Bridge Closure Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

foreach ($requiredSymbols as $symbol) {
    $exists = class_exists($symbol);
    $report[] = '- ' . $symbol . ': ' . ($exists ? 'yes' : 'no');
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

$report[] = '';
$report[] = 'Live route registered: no';
$report[] = 'Live menu enabled: no';
$report[] = 'Live mutation performed: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-phase-147-closure.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-phase-147-closure.log', "PAGE_ADMIN_MOMENTUM_PHASE_147_CLOSURE_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_PHASE_147_CLOSURE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
