<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$warnings = 0;
$report = [];
$requiredFiles = [
    'app/zoosper-page/src/Admin/PageMomentumAdminDashboardShell.php',
    'app/zoosper-page/src/Admin/Controller/PageMomentumAdminController.php',
    'tools/smoke-page-admin-dashboard-indicator-rendering.php',
    'tools/smoke-page-admin-dashboard-visual-shell.php',
    'tools/audit-page-admin-momentum-phase-160-closure.php',
    'docs/development/page-admin-dashboard-indicator-rendering.md',
    'docs/development/page-admin-dashboard-visual-shell.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.60m-z.md',
];
$requiredReports = [
    'var/reports/page-admin-dashboard-visual-shell-smoke.txt',
];
$report[] = '## Phase 1.60 Dashboard Indicator Rendering Closure Audit';
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
$report[] = 'Dashboard visual mode: standalone styled cards';
$report[] = 'Dashboard mode: read-only';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;
$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-phase-160-closure.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-phase-160-closure.log', "PAGE_ADMIN_MOMENTUM_PHASE_160_CLOSURE_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_PHASE_160_CLOSURE_ERRORS {$errors}\n");
echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
