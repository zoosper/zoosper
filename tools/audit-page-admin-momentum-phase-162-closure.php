<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$warnings = 0;
$requiredFiles = [
    'app/zoosper-page/src/Admin/PageAdminDashboardFactProvider.php',
    'app/zoosper-page/src/Admin/PageAdminDashboardFactsGuard.php',
    'app/zoosper-page/src/Admin/Controller/PageMomentumAdminController.php',
    'tools/smoke-page-admin-dashboard-facts.php',
    'tools/audit-page-admin-dashboard-facts.php',
    'tools/audit-page-admin-dashboard-facts-closure.php',
    'tools/audit-page-admin-momentum-phase-162-closure.php',
    'docs/development/page-admin-dashboard-facts.md',
    'docs/development/page-admin-dashboard-facts-closure.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.62m-z.md',
];
$requiredReports = [
    'var/reports/page-admin-dashboard-facts-smoke.txt',
    'var/reports/page-admin-dashboard-facts-audit.txt',
    'var/reports/page-admin-dashboard-facts-closure.txt',
];
$report = ['## Phase 1.62 Page Admin Dashboard Facts Closure Audit', '', 'Generated: ' . gmdate('c'), ''];

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

$report[] = 'Fact scope: live route, live menu, renderer controller, HTTP controller';
$report[] = 'Dashboard mode: read-only';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;
$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-phase-162-closure.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-phase-162-closure.log', "PAGE_ADMIN_MOMENTUM_PHASE_162_CLOSURE_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_PHASE_162_CLOSURE_ERRORS {$errors}\n");
echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
