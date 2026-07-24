<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminAggregationBridge;
use Zoosper\Page\Admin\PageMomentumAdminConsumerHookPreview;

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
$candidatePath = $root . '/app/zoosper-page/config/admin_page_momentum_runtime_candidate.php';

$report[] = '## Page Momentum Admin Consumer Hook Preview Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAdminAggregationBridge::class) || !class_exists(PageMomentumAdminConsumerHookPreview::class)) {
    $report[] = 'Required bridge/preview classes are not autoloadable.';
    $errors++;
} elseif (!is_file($candidatePath)) {
    $report[] = 'Candidate config missing. Run tools/apply-page-admin-momentum-aggregator-candidate.php first.';
    $errors++;
} else {
    $candidate = require $candidatePath;
    $bridgeExport = (new PageMomentumAdminAggregationBridge())->export(is_array($candidate) ? $candidate : []);
    $preview = (new PageMomentumAdminConsumerHookPreview())->preview($bridgeExport);

    foreach ($preview['checks'] as $check => $passed) {
        $report[] = '- ' . $check . ': ' . ($passed ? 'yes' : 'no');
        if (!$passed) {
            $errors++;
        }
    }

    $report[] = 'Ready for live hook: ' . ($preview['readyForLiveHook'] ? 'yes' : 'no');
    $report[] = 'Preview route count: ' . $preview['routeCount'];
    $report[] = 'Preview menu count: ' . $preview['menuCount'];
    $report[] = 'Live mutation performed: ' . ($preview['liveMutation'] ? 'yes' : 'no');

    $reportDir = $root . '/var/reports';
    if (!is_dir($reportDir)) {
        mkdir($reportDir, 0775, true);
    }
    file_put_contents($reportDir . '/page-admin-momentum-consumer-hook-preview.json', json_encode($preview, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
}

$report[] = '';
$report[] = 'Existing aggregator files overwritten: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-consumer-hook-preview.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-consumer-hook-preview.log', "PAGE_ADMIN_MOMENTUM_CONSUMER_HOOK_PREVIEW_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_CONSUMER_HOOK_PREVIEW_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
