<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
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

$report[] = '## Page Admin Launch Readiness Dashboard Smoke';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAdminController::class) || !class_exists(PageAdminLaunchReadinessProvider::class)) {
    $report[] = 'Controller/dashboard provider autoloadable: no';
    $errors++;
} else {
    $html = (new PageMomentumAdminController())->index();
    $checks = [
        'contains Page momentum' => str_contains($html, 'Page momentum'),
        'contains launch-readiness dashboard' => str_contains($html, 'Page Admin launch-readiness dashboard'),
        'contains /admin/page-momentum' => str_contains($html, '/admin/page-momentum'),
        'contains page.manage' => str_contains($html, 'page.manage'),
        'contains PageRenderer report-only candidate' => str_contains($html, 'PageRenderer report-only candidate'),
        'contains Core decoupling readiness' => str_contains($html, 'Core decoupling readiness'),
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
file_put_contents($reportDir . '/page-admin-launch-readiness-dashboard-smoke.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-launch-readiness-dashboard-smoke.log', "PAGE_ADMIN_LAUNCH_READINESS_DASHBOARD_SMOKE_WARNINGS {$warnings}\nPAGE_ADMIN_LAUNCH_READINESS_DASHBOARD_SMOKE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
