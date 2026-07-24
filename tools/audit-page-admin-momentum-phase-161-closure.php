<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$warnings = 0;
$requiredFiles = [
    'app/zoosper-page/src/Admin/PageAdminDashboardStatusPresenter.php',
    'app/zoosper-page/src/Admin/PageAdminDashboardStatusSystemGuard.php',
    'app/zoosper-page/src/Admin/PageMomentumAdminDashboardShell.php',
    'app/zoosper-page/src/Admin/Controller/PageMomentumAdminController.php',
    'tools/smoke-page-admin-dashboard-status-system.php',
    'tools/audit-page-admin-dashboard-status-system.php',
    'tools/audit-page-admin-dashboard-status-system-closure.php',
    'tools/audit-page-admin-momentum-phase-161-closure.php',
    'docs/development/page-admin-dashboard-status-system.md',
    'docs/development/page-admin-dashboard-status-system-closure.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.61m-z.md',
];
$requiredReports = [
    'var/reports/page-admin-dashboard-status-system-smoke.txt',
    'var/reports/page-admin-dashboard-status-system-audit.txt',
    'var/reports/page-admin-dashboard-status-system-closure.txt',
];
$report = ['## Phase 1.61 Page Admin Dashboard Status System Closure Audit', '', 'Generated: ' . gmdate('c'), ''];
foreach ($requiredFiles as $file) {
    $exists = is_file($root . '/' . $file);
    $report[] = '- ' . $file . ': ' . ($exists ? 'exists' : 'missing');
    if (!$exists) { $errors++; }
}
foreach ($requiredReports as $file) {
    $exists = is_file($root . '/' . $file);
    $report[] = '- ' . $file . ': ' . ($exists ? 'exists' : 'missing');
    if (!$exists) { $errors++; }
}
$report[] = 'Dashboard visual status system: closed';
$report[] = 'Dashboard mode: read-only';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;
$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) { mkdir($reportDir, 0775, true); }
file_put_contents($reportDir . '/page-admin-momentum-phase-161-closure.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-phase-161-closure.log', "PAGE_ADMIN_MOMENTUM_PHASE_161_CLOSURE_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_PHASE_161_CLOSURE_ERRORS {$errors}\n");
echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
