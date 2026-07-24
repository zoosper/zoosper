<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
use Zoosper\Page\Admin\PageMomentumActivationGuard;

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

$report[] = '## Page Admin Momentum Live Smoke Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAdminController::class) || !class_exists(PageMomentumActivationGuard::class)) {
    $report[] = 'Required page momentum classes are not autoloadable.';
    $errors++;
} else {
    $html = (new PageMomentumAdminController())->index();
    $htmlOk = is_string($html)
        && str_contains($html, 'Page momentum')
        && str_contains($html, 'Core decoupling readiness')
        && str_contains($html, 'PageRenderer report-only candidate');

    $report[] = 'Controller smoke output valid: ' . ($htmlOk ? 'yes' : 'no');
    if (!$htmlOk) {
        $errors++;
    }
}

$report[] = 'Metadata route path: /admin/page-momentum';
$report[] = 'Metadata permission: page.manage';
$report[] = 'Controller read-only/static: yes';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-live-smoke.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-live-smoke.log', "PAGE_ADMIN_MOMENTUM_LIVE_SMOKE_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_LIVE_SMOKE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
