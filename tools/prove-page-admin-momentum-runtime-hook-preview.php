<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminRuntimeAggregationProvider;
use Zoosper\Page\Admin\PageMomentumAdminRuntimeHookPreview;

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
$configPath = $root . '/app/zoosper-page/config/admin_page_momentum_runtime_aggregation_candidate.php';
$hookPath = $root . '/app/zoosper-page/config/admin_page_momentum_hook_candidate.php';

$report[] = '## Page Momentum Runtime Hook Preview Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAdminRuntimeAggregationProvider::class) || !class_exists(PageMomentumAdminRuntimeHookPreview::class)) {
    $report[] = 'Required runtime provider/preview classes are not autoloadable.';
    $errors++;
} elseif (!is_file($configPath) || !is_file($hookPath)) {
    $report[] = 'Runtime aggregation config or hook candidate config missing.';
    $errors++;
} else {
    $config = require $configPath;
    $hookCandidate = require $hookPath;
    $payload = (new PageMomentumAdminRuntimeAggregationProvider())->provide(
        is_array($config) ? $config : [],
        is_array($hookCandidate) ? $hookCandidate : [],
    );
    $preview = (new PageMomentumAdminRuntimeHookPreview())->preview($payload);

    foreach ($preview['checks'] as $check => $passed) {
        $report[] = '- ' . $check . ': ' . ($passed ? 'yes' : 'no');
        if (!$passed) {
            $errors++;
        }
    }

    $report[] = 'Ready for runtime source hook: ' . ($preview['readyForRuntimeSourceHook'] ? 'yes' : 'no');
    $report[] = 'Preview route count: ' . $preview['routeCount'];
    $report[] = 'Preview menu count: ' . $preview['menuCount'];
    $report[] = 'Rollback steps: ' . count($preview['rollback']);
    $report[] = 'Live mutation performed: ' . ($preview['liveMutation'] ? 'yes' : 'no');

    if (!$preview['readyForRuntimeSourceHook']) {
        $warnings++;
    }

    $reportDir = $root . '/var/reports';
    if (!is_dir($reportDir)) {
        mkdir($reportDir, 0775, true);
    }
    file_put_contents($reportDir . '/page-admin-momentum-runtime-hook-preview.json', json_encode($preview, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
}

$report[] = '';
$report[] = 'Existing aggregator files overwritten: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-runtime-hook-preview.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-runtime-hook-preview.log', "PAGE_ADMIN_MOMENTUM_RUNTIME_HOOK_PREVIEW_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_RUNTIME_HOOK_PREVIEW_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
