<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminSourceHookAdapter;
use Zoosper\Page\Admin\PageMomentumAdminSourceHookPatchPreview;

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
$discoveryFile = $root . '/var/reports/admin-route-menu-aggregator-discovery.json';

$report[] = '## Page Momentum Source Hook Patch Preview Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAdminSourceHookAdapter::class) || !class_exists(PageMomentumAdminSourceHookPatchPreview::class)) {
    $report[] = 'Required adapter/preview classes are not autoloadable.';
    $errors++;
} elseif (!is_file($hookPath)) {
    $report[] = 'Hook candidate config missing. Run tools/generate-page-admin-momentum-hook-candidate.php first.';
    $errors++;
} else {
    $hookCandidate = require $hookPath;
    $adapterExport = (new PageMomentumAdminSourceHookAdapter())->expose(is_array($hookCandidate) ? $hookCandidate : []);
    $discovery = is_file($discoveryFile) ? json_decode((string) file_get_contents($discoveryFile), true) : [];
    $preview = (new PageMomentumAdminSourceHookPatchPreview())->preview($adapterExport, is_array($discovery) ? $discovery : []);

    foreach ($preview['checks'] as $check => $passed) {
        $report[] = '- ' . $check . ': ' . ($passed ? 'yes' : 'no');
        if (!$passed) {
            $errors++;
        }
    }

    $report[] = 'Ready for source patch: ' . ($preview['readyForSourcePatch'] ? 'yes' : 'no');
    $report[] = 'Preview route count: ' . $preview['routeCount'];
    $report[] = 'Preview menu count: ' . $preview['menuCount'];
    $report[] = 'Discovered route files: ' . count($preview['routeFilesDiscovered']);
    $report[] = 'Discovered menu files: ' . count($preview['menuFilesDiscovered']);
    $report[] = 'Live mutation performed: ' . ($preview['liveMutation'] ? 'yes' : 'no');

    if (!$preview['readyForSourcePatch']) {
        $warnings++;
    }

    $reportDir = $root . '/var/reports';
    if (!is_dir($reportDir)) {
        mkdir($reportDir, 0775, true);
    }
    file_put_contents($reportDir . '/page-admin-momentum-source-hook-patch-preview.json', json_encode($preview, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
}

$report[] = '';
$report[] = 'Existing aggregator files overwritten: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-source-hook-patch-preview.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-source-hook-patch-preview.log', "PAGE_ADMIN_MOMENTUM_SOURCE_HOOK_PATCH_PREVIEW_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_SOURCE_HOOK_PATCH_PREVIEW_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
