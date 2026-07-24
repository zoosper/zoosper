<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
use Zoosper\Page\Admin\PageAdminDashboardStatusPresenter;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}
require $autoload;
$errors = 0;
$warnings = 0;
$report = ['## Page Admin Dashboard Status System Smoke', '', 'Generated: ' . gmdate('c'), ''];
if (!class_exists(PageMomentumAdminController::class) || !class_exists(PageAdminDashboardStatusPresenter::class)) {
    $report[] = 'Status system classes autoloadable: no';
    $errors++;
} else {
    $html = (new PageMomentumAdminController())->index();
    $checks = [
        'contains status base class' => str_contains($html, 'zsp-status'),
        'contains ready class' => str_contains($html, 'zsp-status--ready'),
        'contains active class' => str_contains($html, 'zsp-status--active'),
        'contains track class' => str_contains($html, 'zsp-status--track'),
        'contains planned class' => str_contains($html, 'zsp-status--planned'),
        'contains documented class' => str_contains($html, 'zsp-status--documented'),
        'contains in-progress class' => str_contains($html, 'zsp-status--in-progress'),
        'contains dashboard indicators' => str_contains($html, 'Dashboard indicators'),
    ];
    foreach ($checks as $label => $passed) {
        $report[] = '- ' . $label . ': ' . ($passed ? 'yes' : 'no');
        if (!$passed) { $errors++; }
    }
}
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;
$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) { mkdir($reportDir, 0775, true); }
file_put_contents($reportDir . '/page-admin-dashboard-status-system-smoke.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-dashboard-status-system-smoke.log', "PAGE_ADMIN_DASHBOARD_STATUS_SYSTEM_SMOKE_WARNINGS {$warnings}\nPAGE_ADMIN_DASHBOARD_STATUS_SYSTEM_SMOKE_ERRORS {$errors}\n");
echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
