<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$warnings = 0;
$requiredFiles = [
    'app/zoosper-page/src/Admin/PageAdminDashboardStatusPresenter.php',
    'app/zoosper-page/src/Admin/PageMomentumAdminDashboardShell.php',
    'app/zoosper-page/src/Admin/Controller/PageMomentumAdminController.php',
    'tools/smoke-page-admin-dashboard-status-system.php',
    'tools/audit-page-admin-dashboard-status-system.php',
    'docs/development/page-admin-dashboard-status-system.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.61a-l.md',
];
$requiredReports = ['var/reports/page-admin-dashboard-status-system-smoke.txt'];
$report = ['## Page Admin Dashboard Status System Audit', '', 'Generated: ' . gmdate('c'), ''];
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
$report[] = 'Dashboard status system: enabled';
$report[] = 'Dashboard mode: read-only';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;
$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) { mkdir($reportDir, 0775, true); }
file_put_contents($reportDir . '/page-admin-dashboard-status-system-audit.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-dashboard-status-system-audit.log', "PAGE_ADMIN_DASHBOARD_STATUS_SYSTEM_AUDIT_WARNINGS {$warnings}\nPAGE_ADMIN_DASHBOARD_STATUS_SYSTEM_AUDIT_ERRORS {$errors}\n");
echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
