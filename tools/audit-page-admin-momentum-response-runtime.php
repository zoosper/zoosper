<?php

declare(strict_types=1);

use Zoosper\Core\Http\Response;
use Zoosper\Page\Admin\Controller\PageMomentumAdminHttpController;
use Zoosper\Page\Admin\PageMomentumAdminResponseFactory;

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

$report[] = '## Page Momentum Response Runtime Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(Response::class)
    || !class_exists(PageMomentumAdminHttpController::class)
    || !class_exists(PageMomentumAdminResponseFactory::class)) {
    $report[] = 'Required response runtime classes are not autoloadable.';
    $errors++;
} else {
    try {
        $response = (new PageMomentumAdminHttpController())->index();
        $isResponse = $response instanceof Response;
        $report[] = 'HTTP controller returns Response: ' . ($isResponse ? 'yes' : 'no');
        if (!$isResponse) {
            $errors++;
        }
    } catch (Throwable $e) {
        $report[] = 'HTTP controller response creation failed: ' . $e->getMessage();
        $errors++;
    }
}

$routeFiles = array_values(array_filter([
    $root . '/app/zoosper-page/config/admin_routes.php',
    $root . '/app/zoosper-page/config/routes.php',
], 'is_file'));
$usesHttpController = false;
foreach ($routeFiles as $file) {
    $contents = (string) file_get_contents($file);
    if (str_contains($contents, 'PageMomentumAdminHttpController')) {
        $usesHttpController = true;
    }
}
$report[] = 'Live route config uses HTTP controller: ' . ($usesHttpController ? 'yes' : 'no');
if (!$usesHttpController) {
    $errors++;
}

$report[] = 'Expected browser route: /admin/page-momentum';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-response-runtime-audit.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-response-runtime-audit.log', "PAGE_ADMIN_MOMENTUM_RESPONSE_RUNTIME_AUDIT_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_RESPONSE_RUNTIME_AUDIT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
