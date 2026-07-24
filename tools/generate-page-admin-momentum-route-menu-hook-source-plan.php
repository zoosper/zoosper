<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$warnings = 0;
$report = [];
$previewFile = $root . '/var/reports/page-admin-momentum-route-menu-hook-consumer-preview.json';

$report[] = '## Page Momentum Route/Menu Hook Source Plan';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!is_file($previewFile)) {
    $report[] = 'Route/menu hook consumer preview JSON missing. Run tools/prove-page-admin-momentum-route-menu-hook-consumer-preview.php first.';
    $errors++;
} else {
    $preview = json_decode((string) file_get_contents($previewFile), true);
    if (!is_array($preview)) {
        $report[] = 'Route/menu hook consumer preview JSON could not be decoded.';
        $errors++;
    } else {
        $ready = ($preview['readyForConsumerPatch'] ?? false) === true;
        $mutation = ($preview['liveMutation'] ?? true) === true;
        $plan = [
            'readyForConsumerPatch' => $ready,
            'liveMutation' => false,
            'routeFilesDiscovered' => $preview['routeFilesDiscovered'] ?? [],
            'menuFilesDiscovered' => $preview['menuFilesDiscovered'] ?? [],
            'recommendedPatch' => $preview['recommendedPatch'] ?? [],
            'rollback' => $preview['rollback'] ?? [],
        ];

        $report[] = 'Ready for consumer patch: ' . ($ready ? 'yes' : 'no');
        $report[] = 'Route count: ' . (int) ($preview['routeCount'] ?? 0);
        $report[] = 'Menu count: ' . (int) ($preview['menuCount'] ?? 0);
        $report[] = 'Live mutation performed: ' . ($mutation ? 'yes' : 'no');
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
        if ($mutation) {
            $errors++;
        }

        $reportDir = $root . '/var/reports';
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0775, true);
        }
        file_put_contents($reportDir . '/page-admin-momentum-route-menu-hook-source-plan.json', json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    }
}

$report[] = '';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-route-menu-hook-source-plan.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-route-menu-hook-source-plan.log', "PAGE_ADMIN_MOMENTUM_ROUTE_MENU_HOOK_SOURCE_PLAN_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_ROUTE_MENU_HOOK_SOURCE_PLAN_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
