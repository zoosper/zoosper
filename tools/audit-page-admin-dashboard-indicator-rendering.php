<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$warnings = 0;
$report = [];
$requiredFiles = [
    'app/zoosper-page/src/Admin/PageAdminDashboardIndicatorProvider.php',
    'app/zoosper-page/src/Admin/Controller/PageMomentumAdminController.php',
    'tools/smoke-page-admin-dashboard-indicator-rendering.php',
    'tools/audit-page-admin-dashboard-indicator-rendering.php',
    'docs/development/page-admin-dashboard-indicator-rendering.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.60a-l.md',
];
$requiredReports = [
    'var/reports/page-admin-dashboard-indicator-rendering-smoke.txt',
];
$report[] = '## Page Admin Dashboard Indicator Rendering Audit';
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
$report[] = 'Rendered indicator count expected: 6';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;
$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-dashboard-indicator-rendering-audit.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-dashboard-indicator-rendering-audit.log', "PAGE_ADMIN_DASHBOARD_INDICATOR_RENDERING_AUDIT_WARNINGS {$warnings}\nPAGE_ADMIN_DASHBOARD_INDICATOR_RENDERING_AUDIT_ERRORS {$errors}\n");
echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
