<?php

declare(strict_types=1);

/**
 * Page Momentum cleanup closure audit.
 *
 * Read-only final guard for the Page Momentum cleanup arc. It confirms the live
 * dashboard core remains present while removed scaffolding symbols/configs no
 * longer appear in active docs/tools/tests.
 */

$root = dirname(__DIR__);
$reportDir = $root . '/var/reports';

$expectedRuntimeCore = [
    'app/zoosper-page/config/admin_page_momentum.php',
    'app/zoosper-page/config/admin_page_momentum_menu.php',
    'app/zoosper-page/config/admin_page_momentum_routes.php',
    'app/zoosper-page/resources/views/admin/page-momentum.latte',
    'app/zoosper-page/src/Admin/Controller/PageMomentumAdminController.php',
    'app/zoosper-page/src/Admin/Controller/PageMomentumAdminHttpController.php',
    'app/zoosper-page/src/Admin/PageMomentum/PageMomentumDashboardFact.php',
    'app/zoosper-page/src/Admin/PageMomentum/PageMomentumDashboardFactsProvider.php',
    'app/zoosper-page/src/Admin/PageMomentumAdminDashboardShell.php',
    'app/zoosper-page/src/Admin/PageMomentumAdminResponseFactory.php',
    'app/zoosper-page/src/Admin/PageMomentumStatusProvider.php',
];

$removedRuntimeSymbols = [
    'PageMomentumActivationGuard',
    'PageMomentumAdminAggregationBridge',
    'PageMomentumAdminHookProvider',
    'PageMomentumAdminLiveAggregationIntegrator',
    'PageMomentumAdminMenuBridge',
    'PageMomentumAdminRouteBridge',
    'PageMomentumAdminRouteMenuHook',
    'PageMomentumAdminRuntimeAggregationProvider',
    'PageMomentumAdminSourceHookAdapter',
    'PageMomentumAggregatorPatchBuilder',
    'PageMomentumDefinitionProvider',
    'PageMomentumLiveDuplicateGuard',
    'PageMomentumMenuDefinitionProvider',
    'PageMomentumRouteDefinitionProvider',
    'PageMomentumRuntimeBridge',
];

$removedRuntimeConfigNames = [
    'admin_page_momentum_hook_candidate',
    'admin_page_momentum_route_menu_hook',
    'admin_page_momentum_runtime_aggregation_candidate',
    'admin_page_momentum_runtime_candidate',
    'admin_page_momentum_source_hook_adapter',
];

$requiredTools = [
    'tools/audit-page-momentum-cleanup-closure.php',
    'tools/audit-page-momentum-runtime-dependencies.php',
    'tools/audit-repository-lean-hygiene.php',
    'tools/audit-repository-file-count-baseline.php',
];

$errors = [];
$warnings = [];
$observations = [];

foreach ($expectedRuntimeCore as $file) {
    if (!is_file($root . '/' . $file)) {
        $errors[] = 'Missing expected live Page Momentum runtime core file: ' . $file;
    }
}

foreach ($requiredTools as $file) {
    if (!is_file($root . '/' . $file)) {
        $errors[] = 'Missing expected cleanup/hygiene tool: ' . $file;
    }
}

$scanScopes = [
    $root . '/tools',
    $root . '/docs/development',
    $root . '/docs/roadmap',
    $root . '/app/zoosper-core/tests',
    $root . '/app/zoosper-page/tests',
];

$symbolRefs = [];
$configRefs = [];
foreach (listFiles($root, $scanScopes) as $relative => $path) {
    if ($relative === 'tools/audit-page-momentum-cleanup-closure.php') {
        continue;
    }

    $contents = @file_get_contents($path);
    if ($contents === false) {
        continue;
    }

    foreach ($removedRuntimeSymbols as $symbol) {
        if (str_contains($contents, $symbol)) {
            $symbolRefs[$symbol][] = $relative;
        }
    }

    foreach ($removedRuntimeConfigNames as $configName) {
        if (str_contains(strtolower($contents), strtolower($configName))) {
            $configRefs[$configName][] = $relative;
        }
    }
}

foreach ($symbolRefs as $symbol => $files) {
    $errors[] = 'Removed runtime candidate symbol still referenced: ' . $symbol . ' in ' . count(array_unique($files)) . ' file(s).';
}
foreach ($configRefs as $configName => $files) {
    $errors[] = 'Removed runtime candidate config still referenced: ' . $configName . ' in ' . count(array_unique($files)) . ' file(s).';
}

$pageMomentumActiveCoreCount = count(array_filter($expectedRuntimeCore, static fn (string $file): bool => is_file(dirname(__DIR__) . '/' . $file)));
$observations[] = 'Expected live Page Momentum runtime core present: ' . $pageMomentumActiveCoreCount . '/' . count($expectedRuntimeCore);
$observations[] = 'Removed runtime candidate symbol reference groups: ' . count($symbolRefs);
$observations[] = 'Removed runtime candidate config reference groups: ' . count($configRefs);

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$payload = [
    'generatedAt' => gmdate('c'),
    'errors' => $errors,
    'warnings' => $warnings,
    'observations' => $observations,
    'expectedRuntimeCore' => $expectedRuntimeCore,
    'symbolReferences' => $symbolRefs,
    'configReferences' => $configRefs,
];

$report = [];
$report[] = '## Page Momentum Cleanup Closure Audit';
$report[] = '';
$report[] = 'Generated: ' . $payload['generatedAt'];
$report[] = '';
$report[] = 'Errors: ' . count($errors);
$report[] = 'Warnings: ' . count($warnings);
$report[] = 'Observations: ' . count($observations);

$report[] = '';
$report[] = '### Observations';
foreach ($observations as $observation) {
    $report[] = '- ' . $observation;
}

if ($errors !== []) {
    $report[] = '';
    $report[] = '### Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

if ($symbolRefs !== []) {
    $report[] = '';
    $report[] = '### Symbol references';
    foreach ($symbolRefs as $symbol => $files) {
        $report[] = '- ' . $symbol;
        foreach (array_unique($files) as $file) {
            $report[] = '  - ' . $file;
        }
    }
}

if ($configRefs !== []) {
    $report[] = '';
    $report[] = '### Config references';
    foreach ($configRefs as $configName => $files) {
        $report[] = '- ' . $configName;
        foreach (array_unique($files) as $file) {
            $report[] = '  - ' . $file;
        }
    }
}

file_put_contents($reportDir . '/page-momentum-cleanup-closure.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-momentum-cleanup-closure.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit($errors === [] ? 0 : 1);

/**
 * @param list<string> $scopes
 * @return array<string,string>
 */
function listFiles(string $root, array $scopes): array
{
    $files = [];
    foreach ($scopes as $scope) {
        if (!is_dir($scope)) {
            continue;
        }
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scope, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo || !$file->isFile()) {
                continue;
            }
            $path = $file->getPathname();
            $relative = str_replace($root . '/', '', $path);
            $files[$relative] = $path;
        }
    }
    ksort($files);
    return $files;
}
