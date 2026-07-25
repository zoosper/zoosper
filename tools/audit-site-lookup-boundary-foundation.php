<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$reportDir = $root . '/var/reports';
$errors = [];
$warnings = [];
$observations = [];

$expected = [
    'app/zoosper-core/src/Site/ResolvedSite.php',
    'app/zoosper-core/src/Site/SiteLookupInterface.php',
    'app/zoosper-core/src/Site/NullSiteLookup.php',
    'app/zoosper-site/src/Infrastructure/DatabaseSiteLookup.php',
    'app/zoosper-core/tests/Unit/Architecture/SiteLookupBoundaryFoundationTest.php',
    'docs/development/site-lookup-boundary-foundation.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.70m-z.md',
];

foreach ($expected as $file) {
    if (!is_file($root . '/' . $file)) {
        $errors[] = 'Missing expected file: ' . $file;
    }
}

$coreFiles = [
    'app/zoosper-core/src/Site/ResolvedSite.php',
    'app/zoosper-core/src/Site/SiteLookupInterface.php',
    'app/zoosper-core/src/Site/NullSiteLookup.php',
];
foreach ($coreFiles as $file) {
    $source = is_file($root . '/' . $file) ? (string) file_get_contents($root . '/' . $file) : '';
    if (str_contains($source, 'Zoosper\\Site\\')) {
        $errors[] = 'Core-owned site lookup boundary file imports Site module namespace: ' . $file;
    }
}

$adapter = $root . '/app/zoosper-site/src/Infrastructure/DatabaseSiteLookup.php';
if (is_file($adapter)) {
    $source = (string) file_get_contents($adapter);
    if (!str_contains($source, 'SiteLookupInterface')) {
        $errors[] = 'DatabaseSiteLookup does not implement/use SiteLookupInterface.';
    }
    if (!str_contains($source, 'SiteRepository')) {
        $warnings[] = 'DatabaseSiteLookup does not reference SiteRepository; confirm adapter target.';
    }
}

$observations[] = 'Site lookup boundary foundation is contract-only; SiteContextResolver cutover is still pending.';

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$payload = [
    'generatedAt' => gmdate('c'),
    'errors' => $errors,
    'warnings' => $warnings,
    'observations' => $observations,
];

$report = [];
$report[] = '## Site Lookup Boundary Foundation Audit';
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

file_put_contents($reportDir . '/site-lookup-boundary-foundation.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/site-lookup-boundary-foundation.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit($errors === [] ? 0 : 1);
