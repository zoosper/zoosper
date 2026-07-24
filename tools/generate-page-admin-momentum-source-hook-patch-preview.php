<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$warnings = 0;
$report = [];
$previewFile = $root . '/var/reports/page-admin-momentum-source-hook-patch-preview.json';

$report[] = '## Page Momentum Source Hook Patch Preview';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!is_file($previewFile)) {
    $report[] = 'Source hook patch preview JSON missing. Run tools/prove-page-admin-momentum-source-hook-patch-preview.php first.';
    $errors++;
} else {
    $preview = json_decode((string) file_get_contents($previewFile), true);
    if (!is_array($preview)) {
        $report[] = 'Source hook patch preview JSON could not be decoded.';
        $errors++;
    } else {
        $ready = ($preview['readyForSourcePatch'] ?? false) === true;
        $mutation = ($preview['liveMutation'] ?? true) === true;

        $report[] = 'Ready for source patch: ' . ($ready ? 'yes' : 'no');
        $report[] = 'Route count: ' . (int) ($preview['routeCount'] ?? 0);
        $report[] = 'Menu count: ' . (int) ($preview['menuCount'] ?? 0);
        $report[] = 'Live mutation performed: ' . ($mutation ? 'yes' : 'no');
        $report[] = '';
        $report[] = '### Recommended patch';
        foreach (($preview['recommendedPatch'] ?? []) as $step) {
            $report[] = '- ' . $step;
        }
        $report[] = '';
        $report[] = '### Rollback';
        foreach (($preview['rollback'] ?? []) as $step) {
            $report[] = '- ' . $step;
        }

        if (!$ready) {
            $warnings++;
        }
        if ($mutation) {
            $errors++;
        }
    }
}

$report[] = '';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-source-hook-patch-preview-summary.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-source-hook-patch-preview-summary.log', "PAGE_ADMIN_MOMENTUM_SOURCE_HOOK_PATCH_PREVIEW_SUMMARY_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_SOURCE_HOOK_PATCH_PREVIEW_SUMMARY_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
