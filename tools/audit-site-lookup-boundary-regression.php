<?php

declare(strict_types=1);

/**
 * Site lookup boundary regression audit.
 *
 * Read-only guard for the Site lookup boundary after core-feature decoupling.
 */

$root = dirname(__DIR__);
$reportDir = $root . '/var/reports';
$errors = [];
$warnings = [];
$observations = [];

$required = [
    'app/zoosper-core/src/Site/SiteLookupInterface.php',
    'app/zoosper-core/src/Site/NullSiteLookup.php',
    'app/zoosper-core/src/Site/ResolvedSite.php',
    'app/zoosper-core/src/Site/SiteContextResolver.php',
    'app/zoosper-site/src/Infrastructure/DatabaseSiteLookup.php',
    'app/zoosper-core/tests/Unit/Architecture/SiteLookupBoundaryRegressionTest.php',
];

foreach ($required as $file) {
    if (!is_file($root . '/' . $file)) {
        $errors[] = 'Missing expected Site lookup boundary file: ' . $file;
    }
}

$coreSiteDir = $root . '/app/zoosper-core/src/Site';
$violations = [];
$scanned = 0;
if (is_dir($coreSiteDir)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($coreSiteDir, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $scanned++;
        $relative = str_replace($root . '/', '', $file->getPathname());
        $source = (string) file_get_contents($file->getPathname());

        foreach (['Zoosper\\Site\\', 'SiteRepository', 'DbSite'] as $needle) {
            if (str_contains($source, $needle)) {
                $violations[] = $relative . ' contains ' . $needle;
            }
        }
    }
}

foreach ($violations as $violation) {
    $errors[] = 'Core Site boundary regression: ' . $violation;
}

$interface = $root . '/app/zoosper-core/src/Site/SiteLookupInterface.php';
if (is_file($interface)) {
    $source = (string) file_get_contents($interface);
    foreach (['findByHost', 'findActiveByHost', 'findByCode', 'findDefault'] as $method) {
        if (!str_contains($source, 'function ' . $method . '(')) {
            $errors[] = 'SiteLookupInterface missing method: ' . $method;
        }
    }
}

$resolvedSite = $root . '/app/zoosper-core/src/Site/ResolvedSite.php';
if (is_file($resolvedSite)) {
    $source = (string) file_get_contents($resolvedSite);
    foreach (['websiteCode', 'storeCode', 'storeViewCode', 'locale', 'currency', 'pathPrefix'] as $field) {
        if (!str_contains($source, '$' . $field)) {
            $errors[] = 'ResolvedSite missing compatibility field: ' . $field;
        }
    }
}

$observations[] = 'Scanned core Site PHP files: ' . $scanned;
$observations[] = 'Core Site boundary violation count: ' . count($violations);
$observations[] = 'Site lookup boundary regression guard is read-only.';

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
$report[] = '## Site Lookup Boundary Regression Audit';
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

file_put_contents($reportDir . '/site-lookup-boundary-regression.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/site-lookup-boundary-regression.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit($errors === [] ? 0 : 1);
