<?php

declare(strict_types=1);

/**
 * Page fallback runtime cutover readiness audit.
 *
 * Read-only guard before changing ApplicationFactory. This audit confirms the
 * fallback boundary exists and reports the exact remaining direct PageController
 * coupling that the next phase should remove.
 */

$root = dirname(__DIR__);
$reportDir = $root . '/var/reports';
$errors = [];
$warnings = [];
$observations = [];

$files = [
    'fallback_interface' => 'app/zoosper-core/src/Routing/FallbackHandlerInterface.php',
    'null_fallback' => 'app/zoosper-core/src/Routing/NullFallbackHandler.php',
    'page_fallback' => 'app/zoosper-page/src/Routing/PageFallbackHandler.php',
    'page_fallback_adapter' => 'app/zoosper-page/src/Routing/PageFallbackHandlerAdapter.php',
    'application_factory' => 'app/zoosper-core/src/Bootstrap/ApplicationFactory.php',
];

foreach ($files as $label => $file) {
    if (!is_file($root . '/' . $file)) {
        $errors[] = 'Missing expected ' . $label . ' file: ' . $file;
    }
}

$contractFiles = [
    'app/zoosper-core/src/Routing/FallbackHandlerInterface.php',
    'app/zoosper-core/src/Routing/NullFallbackHandler.php',
    'app/zoosper-page/src/Routing/PageFallbackHandler.php',
    'app/zoosper-page/src/Routing/PageFallbackHandlerAdapter.php',
];

foreach ($contractFiles as $file) {
    $path = $root . '/' . $file;
    if (!is_file($path)) {
        continue;
    }

    $source = (string) file_get_contents($path);
    if (!str_contains($source, 'supports(object $request): bool')) {
        $errors[] = 'Fallback contract file does not expose supports(object $request): bool: ' . $file;
    }
    if (!str_contains($source, 'handle(object $request): mixed')) {
        $errors[] = 'Fallback contract file does not expose handle(object $request): mixed: ' . $file;
    }
}

$coreContract = $root . '/app/zoosper-core/src/Routing/FallbackHandlerInterface.php';
if (is_file($coreContract)) {
    $source = (string) file_get_contents($coreContract);
    if (str_contains($source, 'Zoosper\\Page\\')) {
        $errors[] = 'Core fallback contract imports Page namespace.';
    }
}

$applicationFactory = $root . '/app/zoosper-core/src/Bootstrap/ApplicationFactory.php';
$applicationFactoryFindings = [];
if (is_file($applicationFactory)) {
    $source = (string) file_get_contents($applicationFactory);

    $hasDirectPageImport = str_contains($source, 'Zoosper\\Page\\Controller\\PageController');
    $hasPageControllerToken = str_contains($source, 'PageController');
    $hasFallbackInterfaceToken = str_contains($source, 'FallbackHandlerInterface');
    $hasNullFallbackToken = str_contains($source, 'NullFallbackHandler');

    $applicationFactoryFindings = [
        'directPageControllerImport' => $hasDirectPageImport,
        'pageControllerToken' => $hasPageControllerToken,
        'fallbackHandlerInterfaceToken' => $hasFallbackInterfaceToken,
        'nullFallbackHandlerToken' => $hasNullFallbackToken,
    ];

    if ($hasDirectPageImport) {
        $warnings[] = 'ApplicationFactory still imports Zoosper\\Page\\Controller\\PageController. This is the expected Phase 1.69 cutover target.';
    } else {
        $observations[] = 'ApplicationFactory no longer imports PageController directly.';
    }

    if (!$hasFallbackInterfaceToken) {
        $warnings[] = 'ApplicationFactory does not yet reference FallbackHandlerInterface.';
    }
}

$coreFeatureAuditJson = $root . '/var/reports/core-feature-coupling.json';
if (is_file($coreFeatureAuditJson)) {
    $audit = json_decode((string) file_get_contents($coreFeatureAuditJson), true);
    if (is_array($audit)) {
        $observations[] = 'Latest core-feature coupling violation count: ' . (int) ($audit['violationCount'] ?? 0);
        $byModule = $audit['violationsByModule'] ?? [];
        if (is_array($byModule)) {
            foreach ($byModule as $module => $count) {
                $observations[] = 'Latest coupling count for ' . $module . ': ' . (int) $count;
            }
        }
    }
} else {
    $warnings[] = 'core-feature-coupling.json is missing. Run tools/audit-core-feature-coupling.php for the latest coupling count.';
}

$observations[] = 'ApplicationFactory findings: ' . json_encode($applicationFactoryFindings, JSON_UNESCAPED_SLASHES);

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$payload = [
    'generatedAt' => gmdate('c'),
    'errors' => $errors,
    'warnings' => $warnings,
    'observations' => $observations,
    'applicationFactoryFindings' => $applicationFactoryFindings,
];

$report = [];
$report[] = '## Page Fallback Runtime Cutover Readiness Audit';
$report[] = '';
$report[] = 'Generated: ' . $payload['generatedAt'];
$report[] = '';
$report[] = 'Errors: ' . count($errors);
$report[] = 'Warnings: ' . count($warnings);
$report[] = 'Observations: ' . count($observations);

$report[] = '';
$report[] = '### Errors';
foreach ($errors as $error) {
    $report[] = '- ' . $error;
}

$report[] = '';
$report[] = '### Warnings';
foreach ($warnings as $warning) {
    $report[] = '- ' . $warning;
}

$report[] = '';
$report[] = '### Observations';
foreach ($observations as $observation) {
    $report[] = '- ' . $observation;
}

file_put_contents($reportDir . '/page-fallback-runtime-cutover-readiness.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-fallback-runtime-cutover-readiness.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit($errors === [] ? 0 : 1);
