<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$warnings = 0;
$report = [];
$requiredFiles = [
    'app/zoosper-page/src/Admin/PageAdminDashboardIndicatorProvider.php',
    'app/zoosper-page/src/Admin/PageMomentumAdminResponseFactory.php',
    'app/zoosper-page/src/Admin/Controller/PageMomentumAdminHttpController.php',
    'tools/fix-page-admin-momentum-response-controller.php',
    'tools/audit-page-admin-momentum-response-runtime.php',
    'tools/audit-page-admin-momentum-phase-159-closure.php',
    'docs/development/page-admin-dashboard-indicators.md',
    'docs/development/page-admin-momentum-response-runtime-fix.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.59m-z.md',
];
$requiredReports = [
    'var/reports/page-admin-momentum-response-controller-fix.txt',
    'var/reports/page-admin-momentum-response-runtime-audit.txt',
];

$report[] = '## Phase 1.59 Dashboard Indicators Closure and Response Runtime Audit';
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

$report[] = 'Runtime route expected: /admin/page-momentum';
$report[] = 'Runtime controller expected: PageMomentumAdminHttpController';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-phase-159-closure.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-phase-159-closure.log', "PAGE_ADMIN_MOMENTUM_PHASE_159_CLOSURE_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_PHASE_159_CLOSURE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
