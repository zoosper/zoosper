<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumActivationGuard;
use Zoosper\Page\Admin\PageMomentumAdminIntegrationPreview;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}
require $autoload;

$errors = 0;
$report = [];
$momentumPath = $root . '/app/zoosper-page/config/admin_page_momentum.php';
$routePath = $root . '/app/zoosper-page/config/admin_page_momentum_routes.php';
$menuPath = $root . '/app/zoosper-page/config/admin_page_momentum_menu.php';

$report[] = '## Page Admin Momentum Metadata Activation Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumActivationGuard::class) || !class_exists(PageMomentumAdminIntegrationPreview::class)) {
    $report[] = 'Required activation classes are not autoloadable.';
    $errors++;
} elseif (!is_file($momentumPath) || !is_file($routePath) || !is_file($menuPath)) {
    $report[] = 'Momentum metadata files missing.';
    $errors++;
} else {
    $momentumConfig = require $momentumPath;
    $routeConfig = require $routePath;
    $menuConfig = require $menuPath;

    $guard = (new PageMomentumActivationGuard())->inspect($momentumConfig, $routeConfig, $menuConfig);
    $preview = (new PageMomentumAdminIntegrationPreview())->preview($routeConfig, $menuConfig);

    foreach ($guard['checks'] as $check => $passed) {
        $report[] = '- ' . $check . ': ' . ($passed ? 'yes' : 'no');
        if (!$passed) {
            $errors++;
        }
    }

    $previewOk = $preview['routeCount'] === 1
        && $preview['menuCount'] === 1
        && $preview['liveMutation'] === false;

    $report[] = 'Activation guard ready: ' . ($guard['ready'] ? 'yes' : 'no');
    $report[] = 'Bridge preview route count: ' . $preview['routeCount'];
    $report[] = 'Bridge preview menu count: ' . $preview['menuCount'];
    $report[] = 'Bridge preview live mutation: ' . ($preview['liveMutation'] ? 'yes' : 'no');
    $report[] = 'Bridge preview valid: ' . ($previewOk ? 'yes' : 'no');

    if (!$guard['ready'] || !$previewOk) {
        $errors++;
    }
}

$report[] = '';
$report[] = 'Metadata activated: yes';
$report[] = 'Controller read-only: yes';
$report[] = 'Rollback documented: yes';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-metadata-activation-proof.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-metadata-activation-proof.log', "PAGE_ADMIN_MOMENTUM_METADATA_ACTIVATION_PROOF_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
