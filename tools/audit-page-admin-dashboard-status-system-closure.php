<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
use Zoosper\Page\Admin\PageAdminDashboardStatusSystemGuard;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}
require $autoload;

$errors = 0;
$warnings = 0;
$report = ['## Page Admin Dashboard Status System Closure Audit', '', 'Generated: ' . gmdate('c'), ''];

if (!class_exists(PageMomentumAdminController::class) || !class_exists(PageAdminDashboardStatusSystemGuard::class)) {
    $report[] = 'Required dashboard status closure classes are not autoloadable.';
    $errors++;
} else {
    $html = (new PageMomentumAdminController())->index();
    $result = (new PageAdminDashboardStatusSystemGuard())->inspect($html);

    foreach ($result['checks'] as $check => $passed) {
        $report[] = '- ' . $check . ': ' . ($passed ? 'yes' : 'no');
        if (!$passed) {
            $errors++;
        }
    }

    $report[] = 'Status token count: ' . $result['statusTokenCount'];
    $report[] = 'Missing status tokens: ' . ($result['missingTokens'] === [] ? 'none' : implode(', ', $result['missingTokens']));
    $report[] = 'Status system closure valid: ' . ($result['ok'] ? 'yes' : 'no');

    $reportDir = $root . '/var/reports';
    if (!is_dir($reportDir)) {
        mkdir($reportDir, 0775, true);
    }
    file_put_contents($reportDir . '/page-admin-dashboard-status-system-closure.json', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
}

$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;
$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-dashboard-status-system-closure.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-dashboard-status-system-closure.log', "PAGE_ADMIN_DASHBOARD_STATUS_SYSTEM_CLOSURE_WARNINGS {$warnings}\nPAGE_ADMIN_DASHBOARD_STATUS_SYSTEM_CLOSURE_ERRORS {$errors}\n");
echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
