<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
use Zoosper\Page\Admin\Controller\PageMomentumAdminHttpController;

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
$metadataPath = $root . '/app/zoosper-page/config/admin_page_momentum_routes.php';
$liveRouteFiles = array_values(array_filter([
    $root . '/app/zoosper-page/config/admin_routes.php',
    $root . '/app/zoosper-page/config/routes.php',
], 'is_file'));

$report[] = '## Page Momentum Controller Separation Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

$metadataOk = false;
if (is_file($metadataPath)) {
    $config = require $metadataPath;
    $metadataOk = containsController(is_array($config) ? $config : [], PageMomentumAdminController::class);
}
$report[] = 'Metadata uses renderer controller: ' . ($metadataOk ? 'yes' : 'no');
if (!$metadataOk) {
    $errors++;
}

$liveOk = false;
foreach ($liveRouteFiles as $file) {
    $config = require $file;
    if (containsController(is_array($config) ? $config : [], PageMomentumAdminHttpController::class)) {
        $liveOk = true;
    }
    $report[] = '- inspected live route config: ' . str_replace($root . '/', '', $file);
}
$report[] = 'Live route config uses HTTP controller: ' . ($liveOk ? 'yes' : 'no');
if (!$liveOk) {
    $errors++;
}

try {
    $response = (new PageMomentumAdminHttpController())->index();
    $report[] = 'HTTP controller returns object: ' . (is_object($response) ? 'yes' : 'no');
} catch (Throwable $e) {
    $report[] = 'HTTP controller failed: ' . $e->getMessage();
    $errors++;
}

$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-controller-separation-audit.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-controller-separation-audit.log', "PAGE_ADMIN_MOMENTUM_CONTROLLER_SEPARATION_AUDIT_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_CONTROLLER_SEPARATION_AUDIT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);

/**
 * @param mixed $value
 */
function containsController(mixed $value, string $class): bool
{
    if (!is_array($value)) {
        return false;
    }

    foreach ($value as $key => $item) {
        if ($key === 'controller' && $item === $class) {
            return true;
        }
        if (containsController($item, $class)) {
            return true;
        }
    }

    return false;
}
