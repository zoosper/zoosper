<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;

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
$report[] = '## Page Admin Dashboard Indicator Rendering Smoke';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAdminController::class)) {
    $report[] = 'PageMomentumAdminController autoloadable: no';
    $errors++;
} else {
    $html = (new PageMomentumAdminController())->index();
    $checks = [
        'contains dashboard indicators section' => str_contains($html, 'Dashboard indicators'),
        'contains Page CRUD readiness' => str_contains($html, 'Page CRUD readiness'),
        'contains Preview/readiness status' => str_contains($html, 'Preview/readiness status'),
        'contains Sidebar/menu health' => str_contains($html, 'Sidebar/menu health'),
        'contains Route/controller consistency' => str_contains($html, 'Route/controller consistency'),
        'contains Media readiness' => str_contains($html, 'Media readiness'),
        'contains Documentation readiness' => str_contains($html, 'Documentation readiness'),
        'still contains Page Admin launch-readiness dashboard' => str_contains($html, 'Page Admin launch-readiness dashboard'),
        'still contains /admin/page-momentum' => str_contains($html, '/admin/page-momentum'),
        'still contains read-only' => str_contains($html, 'read-only'),
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
file_put_contents($reportDir . '/page-admin-dashboard-indicator-rendering-smoke.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-dashboard-indicator-rendering-smoke.log', "PAGE_ADMIN_DASHBOARD_INDICATOR_RENDERING_SMOKE_WARNINGS {$warnings}\nPAGE_ADMIN_DASHBOARD_INDICATOR_RENDERING_SMOKE_ERRORS {$errors}\n");
echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
