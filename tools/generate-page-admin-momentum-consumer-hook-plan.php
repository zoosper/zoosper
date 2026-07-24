<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$warnings = 0;
$report = [];
$previewFile = $root . '/var/reports/page-admin-momentum-consumer-hook-preview.json';

$report[] = '## Page Momentum Admin Consumer Hook Plan';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!is_file($previewFile)) {
    $report[] = 'Consumer hook preview JSON missing. Run tools/prove-page-admin-momentum-consumer-hook-preview.php first.';
    $errors++;
} else {
    $preview = json_decode((string) file_get_contents($previewFile), true);
    if (!is_array($preview)) {
        $report[] = 'Consumer hook preview JSON could not be decoded.';
        $errors++;
    } else {
        $ready = ($preview['readyForLiveHook'] ?? false) === true;
        $plan = [
            'readyForLiveHook' => $ready,
            'liveMutation' => false,
            'recommendedPatch' => [
                'locate the existing admin route aggregation source discovered in Phase 1.49',
                'consume PageMomentumAdminAggregationBridge::export() or the isolated candidate config from the page module',
                'append exactly one GET /admin/page-momentum route guarded by page.manage',
                'consume the matching menu item guarded by page.manage',
                'run /admin smoke, /admin/page-momentum smoke, Pest, and log checks',
            ],
            'rollback' => [
                'remove the route/menu hook added in the next phase',
                'keep the isolated candidate config for investigation or remove it if needed',
                'set the three page momentum enabled flags to false only if metadata activation itself must be reverted',
                'rerun Pest and inspect nginx/application logs',
            ],
        ];

        $report[] = 'Ready for live hook: ' . ($ready ? 'yes' : 'no');
        $report[] = 'Live mutation performed: no';
        $report[] = '';
        $report[] = '### Recommended patch';
        foreach ($plan['recommendedPatch'] as $step) {
            $report[] = '- ' . $step;
        }
        $report[] = '';
        $report[] = '### Rollback';
        foreach ($plan['rollback'] as $step) {
            $report[] = '- ' . $step;
        }

        if (!$ready) {
            $warnings++;
        }

        $reportDir = $root . '/var/reports';
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0775, true);
        }
        file_put_contents($reportDir . '/page-admin-momentum-consumer-hook-plan.json', json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    }
}

$report[] = '';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-consumer-hook-plan.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-consumer-hook-plan.log', "PAGE_ADMIN_MOMENTUM_CONSUMER_HOOK_PLAN_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_CONSUMER_HOOK_PLAN_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
