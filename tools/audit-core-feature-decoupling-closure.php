<?php

declare(strict_types=1);

/**
 * Core-feature decoupling closure audit.
 *
 * Read-only guard confirming the Page fallback and Site lookup boundary work has
 * removed direct feature-module namespace references from zoosper-core runtime
 * source while keeping the boundary foundation files available.
 */

$root = dirname(__DIR__);
$reportDir = $root . '/var/reports';
$errors = [];
$warnings = [];
$observations = [];

$requiredFiles = [
    'app/zoosper-core/src/Routing/FallbackHandlerInterface.php',
    'app/zoosper-core/src/Routing/NullFallbackHandler.php',
    'app/zoosper-page/src/Routing/PageFallbackHandler.php',
    'app/zoosper-page/src/Routing/PageFallbackHandlerAdapter.php',
    'app/zoosper-core/src/Site/SiteLookupInterface.php',
    'app/zoosper-core/src/Site/ResolvedSite.php',
    'app/zoosper-core/src/Site/NullSiteLookup.php',
    'app/zoosper-site/src/Infrastructure/DatabaseSiteLookup.php',
    'tools/audit-core-feature-coupling.php',
    'tools/plan-core-feature-decoupling-remediation.php',
    'tools/audit-page-fallback-runtime-cutover-readiness.php',
    'tools/audit-site-context-boundary-readiness.php',
    'tools/audit-site-lookup-boundary-foundation.php',
];

foreach ($requiredFiles as $file) {
    if (!is_file($root . '/' . $file)) {
        $errors[] = 'Missing expected decoupling closure file: ' . $file;
    }
}

$coreSrc = $root . '/app/zoosper-core/src';
$forbiddenNamespaces = [
    'Zoosper\\Page\\' => 'Page module',
    'Zoosper\\Site\\' => 'Site module',
    'Zoosper\\Auth\\' => 'Auth module',
    'Zoosper\\Theme\\' => 'Theme module',
    'Zoosper\\Media\\' => 'Media module',
    'Zoosper\\Admin\\' => 'Admin module',
    'Zoosper\\Api\\' => 'API module',
];

$violations = [];
$scanned = 0;
if (is_dir($coreSrc)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($coreSrc, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $scanned++;
        $path = $file->getPathname();
        $relative = str_replace($root . '/', '', $path);
        $source = (string) file_get_contents($path);

        foreach ($forbiddenNamespaces as $needle => $module) {
            if (str_contains($source, $needle)) {
                $violations[] = $relative . ' references ' . $module . ' via ' . $needle;
            }
        }
    }
}

foreach ($violations as $violation) {
    $errors[] = 'Core feature coupling violation remains: ' . $violation;
}

$observations[] = 'Scanned core source PHP files: ' . $scanned;
$observations[] = 'Forbidden feature namespace violations: ' . count($violations);
$observations[] = 'Page fallback boundary files present.';
$observations[] = 'Site lookup boundary files present.';

$coreFeatureJson = $root . '/var/reports/core-feature-coupling.json';
if (is_file($coreFeatureJson)) {
    $json = json_decode((string) file_get_contents($coreFeatureJson), true);
    if (is_array($json)) {
        $observations[] = 'Latest generated core-feature coupling report violationCount: ' . (int) ($json['violationCount'] ?? -1);
    }
} else {
    $warnings[] = 'var/reports/core-feature-coupling.json is missing. Run tools/audit-core-feature-coupling.php to refresh generated report artefacts.';
}

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$payload = [
    'generatedAt' => gmdate('c'),
    'errors' => $errors,
    'warnings' => $warnings,
    'observations' => $observations,
    'violations' => $violations,
];

$report = [];
$report[] = '## Core Feature Decoupling Closure Audit';
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
if ($warnings !== []) {
    $report[] = '';
    $report[] = '### Warnings';
    foreach ($warnings as $warning) {
        $report[] = '- ' . $warning;
    }
}
if ($errors !== []) {
    $report[] = '';
    $report[] = '### Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

file_put_contents($reportDir . '/core-feature-decoupling-closure.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/core-feature-decoupling-closure.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit($errors === [] ? 0 : 1);
