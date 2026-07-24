<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
use Zoosper\Page\Admin\PageAdminDashboardFactProvider;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}
require $autoload;
$errors = 0;
$warnings = 0;
$report = ['## Page Admin Dashboard Facts Smoke', '', 'Generated: ' . gmdate('c'), ''];
if (!class_exists(PageAdminDashboardFactProvider::class) || !class_exists(PageMomentumAdminController::class)) {
    $report[] = 'Dashboard fact classes autoloadable: no';
    $errors++;
} else {
    $facts = (new PageAdminDashboardFactProvider())->facts();
    $html = (new PageMomentumAdminController())->index();
    $checks = [
        'four facts' => count($facts) === 4,
        'contains real dashboard facts section' => str_contains($html, 'Real dashboard facts'),
        'contains live route fact' => str_contains($html, 'Live route fact'),
        'contains live menu fact' => str_contains($html, 'Live menu fact'),
        'contains renderer controller fact' => str_contains($html, 'Renderer controller fact'),
        'contains HTTP controller fact' => str_contains($html, 'HTTP controller fact'),
        'still contains dashboard indicators' => str_contains($html, 'Dashboard indicators'),
        'still contains status badges' => str_contains($html, 'zsp-status'),
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
file_put_contents($reportDir . '/page-admin-dashboard-facts-smoke.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-dashboard-facts-smoke.log', "PAGE_ADMIN_DASHBOARD_FACTS_SMOKE_WARNINGS {$warnings}\nPAGE_ADMIN_DASHBOARD_FACTS_SMOKE_ERRORS {$errors}\n");
echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
