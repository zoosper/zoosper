<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
use Zoosper\Page\Admin\PageMomentumStatusProvider;

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

$report[] = '## Page Momentum Live Panel Smoke';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAdminController::class) || !class_exists(PageMomentumStatusProvider::class)) {
    $report[] = 'Controller/status provider autoloadable: no';
    $errors++;
} else {
    $html = (new PageMomentumAdminController())->index();
    $checks = [
        'contains title' => str_contains($html, 'Page momentum'),
        'contains route' => str_contains($html, '/admin/page-momentum'),
        'contains permission' => str_contains($html, 'page.manage'),
        'contains read-only mode' => str_contains($html, 'read-only'),
        'contains rollback status' => str_contains($html, 'Rollback'),
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
file_put_contents($reportDir . '/page-admin-momentum-live-panel-smoke.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-live-panel-smoke.log', "PAGE_ADMIN_MOMENTUM_LIVE_PANEL_SMOKE_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_LIVE_PANEL_SMOKE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
