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

$routeRoot = $routeConfig['page_momentum_routes'] ?? [];
$menuRoot = $menuConfig['page_momentum_menu'] ?? [];
$routeEnabled = is_array($routeRoot) && isset($routeRoot['enabled']) && is_bool($routeRoot['enabled']) ? $routeRoot['enabled'] : null;
$menuEnabled = is_array($menuRoot) && isset($menuRoot['enabled']) && is_bool($menuRoot['enabled']) ? $menuRoot['enabled'] : null;

$report[] = '';
$report[] = '- route metadata enabled: ' . ($routeEnabled === true ? 'yes' : ($routeEnabled === false ? 'no' : 'invalid'));
$report[] = '- menu metadata enabled: ' . ($menuEnabled === true ? 'yes' : ($menuEnabled === false ? 'no' : 'invalid'));

if ($routeEnabled === null || $menuEnabled === null) {
    $errors++;
}

$previewFile = $root . '/var/reports/page-admin-momentum-cutover-preview.json';
if (is_file($previewFile)) {
    $preview = json_decode((string) file_get_contents($previewFile), true);
    $previewReady = is_array($preview) && ($preview['preflightReady'] ?? false) === true;
    $previewMutation = is_array($preview) && ($preview['liveMutation'] ?? true) === true;
    $routeCount = is_array($preview) && isset($preview['wouldRegisterRoutes']) && is_array($preview['wouldRegisterRoutes']) ? count($preview['wouldRegisterRoutes']) : 0;
    $menuCount = is_array($preview) && isset($preview['wouldRegisterMenuItems']) && is_array($preview['wouldRegisterMenuItems']) ? count($preview['wouldRegisterMenuItems']) : 0;

    $report[] = '- cutover preview preflight ready: ' . ($previewReady ? 'yes' : 'no');
    $report[] = '- cutover preview route count: ' . $routeCount;
    $report[] = '- cutover preview menu count: ' . $menuCount;
    $report[] = '- cutover preview live mutation: ' . ($previewMutation ? 'yes' : 'no');

    if (!$previewReady || $previewMutation || $routeCount !== 1 || $menuCount !== 1) {
        $errors++;
    }
}

$report[] = 'Live route registered: metadata-active';
$report[] = 'Live menu enabled: metadata-active';
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
