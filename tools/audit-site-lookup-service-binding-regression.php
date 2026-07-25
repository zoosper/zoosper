<?php

declare(strict_types=1);

/**
 * Site lookup service binding regression audit.
 *
 * Read-only guard to keep the SiteLookupInterface -> DatabaseSiteLookup binding
 * visible in the Site module config and out of core runtime ownership.
 */

$root = dirname(__DIR__);
$reportDir = $root . '/var/reports';
$errors = [];
$warnings = [];
$observations = [];

$siteServicesFile = $root . '/app/zoosper-site/config/services.php';
$coreServicesFile = $root . '/app/zoosper-core/config/services.php';
$coreSiteDir = $root . '/app/zoosper-core/src/Site';

if (!is_file($siteServicesFile)) {
    $errors[] = 'Missing Site services config: app/zoosper-site/config/services.php';
} else {
    $siteServices = (string) file_get_contents($siteServicesFile);
    foreach (['SiteLookupInterface::class', 'DatabaseSiteLookup', 'SiteRepository::class'] as $needle) {
        if (!str_contains($siteServices, $needle)) {
            $errors[] = 'Site services config missing expected binding token: ' . $needle;
        }
    }
}

if (is_file($coreServicesFile)) {
    $coreServices = (string) file_get_contents($coreServicesFile);
    foreach (['DatabaseSiteLookup', 'Zoosper\\Site\\Infrastructure\\DatabaseSiteLookup'] as $needle) {
        if (str_contains($coreServices, $needle)) {
            $errors[] = 'Core services config should not own Site lookup adapter binding: ' . $needle;
        }
    }
} else {
    $warnings[] = 'Core services config missing: app/zoosper-core/config/services.php';
}

$coreViolations = [];
if (is_dir($coreSiteDir)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($coreSiteDir, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $relative = str_replace($root . '/', '', $file->getPathname());
        $source = (string) file_get_contents($file->getPathname());
        foreach (['Zoosper\\Site\\', 'SiteRepository', 'DbSite'] as $needle) {
            if (str_contains($source, $needle)) {
                $coreViolations[] = $relative . ' contains ' . $needle;
            }
        }
    }
} else {
    $warnings[] = 'Core Site source directory missing: app/zoosper-core/src/Site';
}

foreach ($coreViolations as $violation) {
    $errors[] = 'Core Site boundary regression: ' . $violation;
}

$observations[] = 'Site lookup binding regression audit completed.';
$observations[] = 'Core Site violation count: ' . count($coreViolations);

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$payload = [
    'generatedAt' => gmdate('c'),
    'errors' => $errors,
    'warnings' => $warnings,
    'observations' => $observations,
    'coreViolations' => $coreViolations,
];

$report = [];
$report[] = '## Site Lookup Service Binding Regression Audit';
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

file_put_contents($reportDir . '/site-lookup-service-binding-regression.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/site-lookup-service-binding-regression.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit($errors === [] ? 0 : 1);
