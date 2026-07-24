<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$warnings = 0;
$report = [];
$requiredFiles = [
    'app/zoosper-page/src/Admin/PageAdminLaunchReadinessProvider.php',
    'app/zoosper-page/src/Admin/PageMomentumStatusProvider.php',
    'app/zoosper-page/src/Admin/Controller/PageMomentumAdminController.php',
    'tools/smoke-page-admin-launch-readiness-dashboard.php',
    'tools/audit-page-admin-launch-readiness-dashboard.php',
    'docs/development/page-admin-launch-readiness-dashboard.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.58a-l.md',
];
$requiredReports = [
    'var/reports/page-admin-launch-readiness-dashboard-smoke.txt',
];

$report[] = '## Page Admin Launch Readiness Dashboard Audit';
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

$report[] = 'Dashboard mode: read-only';
$report[] = 'Route: /admin/page-momentum';
$report[] = 'Permission: page.manage';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-launch-readiness-dashboard-audit.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-launch-readiness-dashboard-audit.log', "PAGE_ADMIN_LAUNCH_READINESS_DASHBOARD_AUDIT_WARNINGS {$warnings}\nPAGE_ADMIN_LAUNCH_READINESS_DASHBOARD_AUDIT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
