<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
use Zoosper\Page\Admin\PageMomentumAdminDashboardShell;

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
$report[] = '## Page Admin Dashboard Visual Shell Smoke';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAdminController::class) || !class_exists(PageMomentumAdminDashboardShell::class)) {
    $report[] = 'Dashboard controller/shell autoloadable: no';
    $errors++;
} else {
    $html = (new PageMomentumAdminController())->index();
    $checks = [
        'contains doctype' => str_contains($html, '<!doctype html>'),
        'contains style block' => str_contains($html, '<style>'),
        'contains admin shell' => str_contains($html, 'zoosper-admin-shell'),
        'contains card css' => str_contains($html, '.zoosper-admin-card'),
        'contains grid css' => str_contains($html, '.zoosper-admin-grid'),
        'contains dashboard indicators' => str_contains($html, 'Dashboard indicators'),
        'contains Page CRUD readiness' => str_contains($html, 'Page CRUD readiness'),
        'contains read-only' => str_contains($html, 'read-only'),
    ];

    foreach ($checks as $label => $passed) {
        $report[] = '- ' . $label . ': ' . ($passed ? 'yes' : 'no');
        if (!$passed) {
            $errors++;
        }
    }
}

$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;
$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-dashboard-visual-shell-smoke.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-dashboard-visual-shell-smoke.log', "PAGE_ADMIN_DASHBOARD_VISUAL_SHELL_SMOKE_WARNINGS {$warnings}\nPAGE_ADMIN_DASHBOARD_VISUAL_SHELL_SMOKE_ERRORS {$errors}\n");
echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
