<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminSourceHookAdapter;

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
$hookPath = $root . '/app/zoosper-page/config/admin_page_momentum_hook_candidate.php';

$report[] = '## Page Momentum Source Hook Adapter Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAdminSourceHookAdapter::class)) {
    $report[] = 'Source hook adapter autoloadable: no';
    $errors++;
} elseif (!is_file($hookPath)) {
    $report[] = 'Hook candidate config missing. Run tools/generate-page-admin-momentum-hook-candidate.php first.';
    $errors++;
} else {
    $hookCandidate = require $hookPath;
    $exposed = (new PageMomentumAdminSourceHookAdapter())->expose(is_array($hookCandidate) ? $hookCandidate : []);

    $valid = $exposed['routeCount'] === 1
        && $exposed['menuCount'] === 1
        && ($exposed['routes'][0]['name'] ?? '') === 'admin.page_momentum.index'
        && ($exposed['menuItems'][0]['route'] ?? '') === 'admin.page_momentum.index'
        && $exposed['liveMutation'] === false;

    $report[] = 'Source hook adapter autoloadable: yes';
    $report[] = 'Exposed route count: ' . $exposed['routeCount'];
    $report[] = 'Exposed menu count: ' . $exposed['menuCount'];
    $report[] = 'Live mutation performed: ' . ($exposed['liveMutation'] ? 'yes' : 'no');
    $report[] = 'Source hook adapter valid: ' . ($valid ? 'yes' : 'no');

    if (!$valid) {
        $errors++;
    }

    $reportDir = $root . '/var/reports';
    if (!is_dir($reportDir)) {
        mkdir($reportDir, 0775, true);
    }
    file_put_contents($reportDir . '/page-admin-momentum-source-hook-adapter.json', json_encode($exposed, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
}

$report[] = '';
$report[] = 'Existing aggregator files overwritten: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-source-hook-adapter.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-source-hook-adapter.log', "PAGE_ADMIN_MOMENTUM_SOURCE_HOOK_ADAPTER_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_SOURCE_HOOK_ADAPTER_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
