<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
use Zoosper\Page\Admin\PageAdminDashboardFactProvider;
use Zoosper\Page\Admin\PageAdminDashboardFactsGuard;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}
require $autoload;

$errors = 0;
$warnings = 0;
$report = ['## Page Admin Dashboard Facts Closure Audit', '', 'Generated: ' . gmdate('c'), ''];

if (!class_exists(PageAdminDashboardFactProvider::class)
    || !class_exists(PageAdminDashboardFactsGuard::class)
    || !class_exists(PageMomentumAdminController::class)) {
    $report[] = 'Required dashboard fact closure classes are not autoloadable.';
    $errors++;
} else {
    $facts = (new PageAdminDashboardFactProvider())->facts();
    $html = (new PageMomentumAdminController())->index();
    $result = (new PageAdminDashboardFactsGuard())->inspect($facts, $html);

    foreach ($result['checks'] as $check => $passed) {
        $report[] = '- ' . $check . ': ' . ($passed ? 'yes' : 'no');
        if (!$passed) {
            $errors++;
        }
    }

    $report[] = 'Fact count: ' . $result['factCount'];
    $report[] = 'Missing labels: ' . ($result['missingLabels'] === [] ? 'none' : implode(', ', $result['missingLabels']));
    $report[] = 'Unknown statuses: ' . ($result['unknownStatuses'] === [] ? 'none' : implode(', ', $result['unknownStatuses']));
    $report[] = 'Dashboard facts closure valid: ' . ($result['ok'] ? 'yes' : 'no');

    $reportDir = $root . '/var/reports';
    if (!is_dir($reportDir)) {
        mkdir($reportDir, 0775, true);
    }
    file_put_contents($reportDir . '/page-admin-dashboard-facts-closure.json', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
}

$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;
$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-dashboard-facts-closure.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-dashboard-facts-closure.log', "PAGE_ADMIN_DASHBOARD_FACTS_CLOSURE_WARNINGS {$warnings}\nPAGE_ADMIN_DASHBOARD_FACTS_CLOSURE_ERRORS {$errors}\n");
echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
