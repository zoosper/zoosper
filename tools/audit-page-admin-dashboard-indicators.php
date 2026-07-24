<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$warnings = 0;
$report = [];
$requiredFiles = [
    'app/zoosper-page/src/Admin/PageAdminDashboardIndicatorProvider.php',
    'app/zoosper-page/src/Admin/PageAdminLaunchReadinessProvider.php',
    'tools/smoke-page-admin-dashboard-indicators.php',
    'tools/audit-page-admin-dashboard-indicators.php',
    'docs/development/page-admin-dashboard-indicators.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.59a-l.md',
];
$requiredReports = [
    'var/reports/page-admin-dashboard-indicators-smoke.txt',
];

$report[] = '## Page Admin Dashboard Indicators Audit';
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
$report[] = 'Indicator scope: page CRUD, preview, sidebar/menu, route/controller, media, documentation';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-dashboard-indicators-audit.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-dashboard-indicators-audit.log', "PAGE_ADMIN_DASHBOARD_INDICATORS_AUDIT_WARNINGS {$warnings}\nPAGE_ADMIN_DASHBOARD_INDICATORS_AUDIT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
