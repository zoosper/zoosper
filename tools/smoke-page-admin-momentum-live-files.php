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

$report[] = '## Page Momentum Live Files Smoke';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAdminController::class)) {
    $report[] = 'Controller autoloadable: no';
    $errors++;
} else {
    $html = (new PageMomentumAdminController())->index();
    $ok = is_string($html) && str_contains($html, 'Page momentum') && str_contains($html, 'Core decoupling readiness');
    $report[] = 'Controller autoloadable: yes';
    $report[] = 'Controller output smoke: ' . ($ok ? 'yes' : 'no');
    if (!$ok) {
        $errors++;
    }
}

$report[] = 'Route path expected: /admin/page-momentum';
$report[] = 'Permission expected: page.manage';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-live-files-smoke.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-live-files-smoke.log', "PAGE_ADMIN_MOMENTUM_LIVE_FILES_SMOKE_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_LIVE_FILES_SMOKE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
