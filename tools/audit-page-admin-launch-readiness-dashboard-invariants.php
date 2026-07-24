<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
use Zoosper\Page\Admin\PageAdminLaunchReadinessDashboardGuard;
use Zoosper\Page\Admin\PageAdminLaunchReadinessProvider;

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

$report[] = '## Page Admin Launch Readiness Dashboard Invariant Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageAdminLaunchReadinessProvider::class)
    || !class_exists(PageAdminLaunchReadinessDashboardGuard::class)
    || !class_exists(PageMomentumAdminController::class)) {
    $report[] = 'Required dashboard classes are not autoloadable.';
    $errors++;
} else {
    $sections = (new PageAdminLaunchReadinessProvider())->sections();
    $html = (new PageMomentumAdminController())->index();
    $result = (new PageAdminLaunchReadinessDashboardGuard())->inspect($sections, $html);

    foreach ($result['checks'] as $check => $passed) {
        $report[] = '- ' . $check . ': ' . ($passed ? 'yes' : 'no');
        if (!$passed) {
            $errors++;
        }
    }

    $report[] = 'Section count: ' . $result['sectionCount'];
    $report[] = 'Missing headings: ' . ($result['missingHeadings'] === [] ? 'none' : implode(', ', $result['missingHeadings']));
    $report[] = 'Dashboard invariants valid: ' . ($result['ok'] ? 'yes' : 'no');

    $reportDir = $root . '/var/reports';
    if (!is_dir($reportDir)) {
        mkdir($reportDir, 0775, true);
    }
    file_put_contents($reportDir . '/page-admin-launch-readiness-dashboard-invariants.json', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
}

$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-launch-readiness-dashboard-invariants.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-launch-readiness-dashboard-invariants.log', "PAGE_ADMIN_LAUNCH_READINESS_DASHBOARD_INVARIANTS_WARNINGS {$warnings}\nPAGE_ADMIN_LAUNCH_READINESS_DASHBOARD_INVARIANTS_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
