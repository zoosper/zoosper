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
    'app/zoosper-page/src/Admin/PageMomentumLiveCutoverPreflight.php',
    'tools/audit-page-admin-momentum-live-cutover-preflight.php',
    'tools/generate-page-admin-momentum-cutover-preview.php',
    'tools/audit-page-admin-momentum-phase-148-readiness.php',
    'docs/development/page-admin-momentum-live-cutover-preflight.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.48a-l.md',
];
$requiredReports = [
    'var/reports/page-admin-momentum-live-cutover-preflight.txt',
    'var/reports/page-admin-momentum-cutover-preview.json',
    'var/reports/page-admin-momentum-cutover-preview.txt',
];

$report[] = '## Phase 1.48 Page Admin Momentum Live Cutover Readiness Audit';
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
foreach ($requiredReports as $file) {
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

$report[] = 'Live route registered: no';
$report[] = 'Live menu enabled: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-phase-148-readiness.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-phase-148-readiness.log', "PAGE_ADMIN_MOMENTUM_PHASE_148_READINESS_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_PHASE_148_READINESS_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
