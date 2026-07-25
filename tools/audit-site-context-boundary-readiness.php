<?php

declare(strict_types=1);

/**
 * Site context boundary readiness audit.
 *
 * Read-only guard for the next core-feature decoupling target. It reports the
 * remaining direct core references to the Site module and prepares a safe plan
 * for moving those dependencies behind core-owned contracts.
 */

$root = dirname(__DIR__);
$reportDir = $root . '/var/reports';
$errors = [];
$warnings = [];
$observations = [];
$findings = [];

$targets = [
    'resolver' => 'app/zoosper-core/src/Site/SiteContextResolver.php',
    'factory' => 'app/zoosper-core/src/Site/SiteContextResolverFactory.php',
];

foreach ($targets as $label => $relative) {
    $path = $root . '/' . $relative;
    if (!is_file($path)) {
        $errors[] = 'Missing expected ' . $label . ' file: ' . $relative;
        continue;
    }

    $source = (string) file_get_contents($path);
    $lines = preg_split('/\R/', $source) ?: [];
    $siteLines = [];
    $repositoryLines = [];
    $dbSiteLines = [];
    $serviceResolverLines = [];

    foreach ($lines as $index => $line) {
        if (str_contains($line, 'Zoosper\\Site\\')) {
            $siteLines[] = ['line' => $index + 1, 'source' => trim($line)];
        }
        if (str_contains($line, 'SiteRepository')) {
            $repositoryLines[] = ['line' => $index + 1, 'source' => trim($line)];
        }
        if (str_contains($line, 'DbSite') || str_contains($line, 'Model\\Site')) {
            $dbSiteLines[] = ['line' => $index + 1, 'source' => trim($line)];
        }
        if (str_contains($line, 'SiteResolver')) {
            $serviceResolverLines[] = ['line' => $index + 1, 'source' => trim($line)];
        }
    }

    $findings[$relative] = [
        'siteNamespaceReferences' => $siteLines,
        'siteRepositoryLines' => $repositoryLines,
        'dbSiteLines' => $dbSiteLines,
        'siteResolverLines' => $serviceResolverLines,
        'siteNamespaceReferenceCount' => count($siteLines),
        'siteRepositoryLineCount' => count($repositoryLines),
        'dbSiteLineCount' => count($dbSiteLines),
        'siteResolverLineCount' => count($serviceResolverLines),
    ];

    if ($siteLines !== []) {
        $warnings[] = $relative . ' still references Site module namespace directly: ' . count($siteLines);
    }
}

$coreFeatureAuditJson = $root . '/var/reports/core-feature-coupling.json';
if (is_file($coreFeatureAuditJson)) {
    $audit = json_decode((string) file_get_contents($coreFeatureAuditJson), true);
    if (is_array($audit)) {
        $byModule = $audit['violationsByModule'] ?? [];
        if (is_array($byModule)) {
            foreach ($byModule as $module => $count) {
                $observations[] = 'Latest coupling count for ' . $module . ': ' . (int) $count;
            }
        }
    }
} else {
    $warnings[] = 'core-feature-coupling.json is missing. Run tools/audit-core-feature-coupling.php for latest counts.';
}

$observations[] = 'Recommended core-owned contract candidate: SiteLookupInterface or SiteContextLookupInterface.';
$observations[] = 'Recommended Site module adapter candidate: DatabaseSiteLookup backed by SiteRepository.';
$observations[] = 'Do not cut over until constructor/factory wiring is explicitly patched and tested.';

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$payload = [
    'generatedAt' => gmdate('c'),
    'errors' => $errors,
    'warnings' => $warnings,
    'observations' => $observations,
    'findings' => $findings,
];

$report = [];
$report[] = '## Site Context Boundary Readiness Audit';
$report[] = '';
$report[] = 'Generated: ' . $payload['generatedAt'];
$report[] = '';
$report[] = 'Errors: ' . count($errors);
$report[] = 'Warnings: ' . count($warnings);
$report[] = 'Observations: ' . count($observations);
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
$report[] = '';
$report[] = '### Findings';
foreach ($findings as $file => $fileFindings) {
    $report[] = '#### ' . $file;
    $report[] = '- Site namespace references: ' . $fileFindings['siteNamespaceReferenceCount'];
    foreach ($fileFindings['siteNamespaceReferences'] as $item) {
        $report[] = '  - line ' . $item['line'] . ': ' . $item['source'];
    }
    $report[] = '- SiteRepository lines: ' . $fileFindings['siteRepositoryLineCount'];
    foreach ($fileFindings['siteRepositoryLines'] as $item) {
        $report[] = '  - line ' . $item['line'] . ': ' . $item['source'];
    }
    $report[] = '- DbSite lines: ' . $fileFindings['dbSiteLineCount'];
    foreach ($fileFindings['dbSiteLines'] as $item) {
        $report[] = '  - line ' . $item['line'] . ': ' . $item['source'];
    }
}
if ($errors !== []) {
    $report[] = '';
    $report[] = '### Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

file_put_contents($reportDir . '/site-context-boundary-readiness.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/site-context-boundary-readiness.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit($errors === [] ? 0 : 1);
