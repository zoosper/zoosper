<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminHookConsumerPreview;

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

$report[] = '## Page Admin Momentum Hook Consumer Preview Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAdminHookConsumerPreview::class)) {
    $report[] = 'Hook consumer preview autoloadable: no';
    $errors++;
} elseif (!is_file($hookPath)) {
    $report[] = 'Hook candidate config missing. Run tools/generate-page-admin-momentum-hook-candidate.php first.';
    $errors++;
} else {
    $hookCandidate = require $hookPath;
    $preview = (new PageMomentumAdminHookConsumerPreview())->preview(is_array($hookCandidate) ? $hookCandidate : []);

    foreach ($preview['checks'] as $check => $passed) {
        $report[] = '- ' . $check . ': ' . ($passed ? 'yes' : 'no');
        if (!$passed) {
            $errors++;
        }
    }

    $report[] = 'Ready for source hook: ' . ($preview['readyForSourceHook'] ? 'yes' : 'no');
    $report[] = 'Preview route count: ' . $preview['routeCount'];
    $report[] = 'Preview menu count: ' . $preview['menuCount'];
    $report[] = 'Live mutation performed: ' . ($preview['liveMutation'] ? 'yes' : 'no');

    $reportDir = $root . '/var/reports';
    if (!is_dir($reportDir)) {
        mkdir($reportDir, 0775, true);
    }
    file_put_contents($reportDir . '/page-admin-momentum-hook-consumer-preview.json', json_encode($preview, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
}

$report[] = '';
$report[] = 'Existing aggregator files overwritten: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-hook-consumer-preview.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-hook-consumer-preview.log', "PAGE_ADMIN_MOMENTUM_HOOK_CONSUMER_PREVIEW_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_HOOK_CONSUMER_PREVIEW_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
