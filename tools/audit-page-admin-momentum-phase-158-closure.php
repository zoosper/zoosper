<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$warnings = 0;
$report = [];
$requiredFiles = [
    'app/zoosper-page/src/Admin/PageAdminLaunchReadinessProvider.php',
    'app/zoosper-page/src/Admin/PageAdminLaunchReadinessDashboardGuard.php',
    'app/zoosper-page/src/Admin/Controller/PageMomentumAdminController.php',
    'tools/smoke-page-admin-launch-readiness-dashboard.php',
    'tools/audit-page-admin-launch-readiness-dashboard.php',
    'tools/audit-page-admin-launch-readiness-dashboard-invariants.php',
    'tools/audit-page-admin-momentum-phase-158-closure.php',
    'docs/development/page-admin-launch-readiness-dashboard.md',
    'docs/development/page-admin-launch-readiness-dashboard-closure.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.58m-z.md',
];
$requiredReports = [
    'var/reports/page-admin-launch-readiness-dashboard-smoke.txt',
    'var/reports/page-admin-launch-readiness-dashboard-audit.txt',
    'var/reports/page-admin-launch-readiness-dashboard-invariants.txt',
];

$report[] = '## Phase 1.58 Page Admin Launch Readiness Dashboard Closure Audit';
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

$report[] = 'Dashboard route: /admin/page-momentum';
$report[] = 'Dashboard permission: page.manage';
$report[] = 'Dashboard mode: read-only';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-phase-158-closure.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-phase-158-closure.log', "PAGE_ADMIN_MOMENTUM_PHASE_158_CLOSURE_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_PHASE_158_CLOSURE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
